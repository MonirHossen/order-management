<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    /**
     * Get all orders with filters
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items.product', 'items.variant']);

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        // Filter by payment status
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Filter by date range
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        // Recent orders
        if (!empty($filters['recent_days'])) {
            $query->recent($filters['recent_days']);
        }

        // Search by order number or customer name
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhere('shipping_name', 'like', "%{$filters['search']}%")
                  ->orWhere('shipping_email', 'like', "%{$filters['search']}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find order by ID
     */
    public function findById(int $id): ?Order
    {
        return Order::with(['user', 'items.product', 'items.variant', 'statusHistories.changedBy', 'invoices'])
            ->find($id);
    }

    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with(['user', 'items.product', 'items.variant'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    /**
     * Create order
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update order
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh();
    }

    /**
     * Delete order
     */
    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    /**
     * Get orders by vendor
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::whereHas('items.product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })
        ->with(['user', 'items' => function ($query) use ($vendorId) {
            $query->whereHas('product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })->with('product', 'variant');
        }])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

    /**
     * Get order statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = Order::query();

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        return [
            'total_orders' => $query->count(),
            'pending_orders' => (clone $query)->status('pending')->count(),
            'processing_orders' => (clone $query)->status('processing')->count(),
            'shipped_orders' => (clone $query)->status('shipped')->count(),
            'delivered_orders' => (clone $query)->status('delivered')->count(),
            'cancelled_orders' => (clone $query)->status('cancelled')->count(),
            'total_revenue' => (clone $query)->sum('total_amount'),
            'average_order_value' => (clone $query)->avg('total_amount'),
        ];
    }
}