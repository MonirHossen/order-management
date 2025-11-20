<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\OrderCancelled;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected ProductRepository $productRepository,
        protected InventoryService $inventoryService,
        protected InvoiceService $invoiceService
    ) {
    }

    /**
     * Create order
     */
    public function createOrder(array $data, int $userId): Order
    {
        DB::beginTransaction();
        try {
            // Validate stock availability
            $this->validateStockAvailability($data['items']);

            // Calculate totals
            $totals = $this->calculateTotals($data['items'], $data);

            // Create order
            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'shipping_fee' => $totals['shipping_fee'],
                'discount' => $totals['discount'],
                'total_amount' => $totals['total'],
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => 'pending',
                'shipping_name' => $data['shipping_name'],
                'shipping_email' => $data['shipping_email'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'] ?? null,
                'shipping_country' => $data['shipping_country'],
                'shipping_postal_code' => $data['shipping_postal_code'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create order items and deduct inventory
            foreach ($data['items'] as $item) {
                $product = $this->productRepository->findById($item['product_id']);
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'variant_details' => $item['variant_id'] ? $product->variants()->find($item['variant_id'])->attributes : null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $item['quantity'],
                ]);

                // Deduct inventory
                $this->inventoryService->deductStock(
                    $product,
                    $item['quantity'],
                    $item['variant_id'] ?? null
                );
            }

            // Create initial status history
            $this->createStatusHistory($order, 'pending', 'Order created', $userId);

            // Generate invoice
            $this->invoiceService->generateInvoice($order);

            // Dispatch event
            event(new OrderCreated($order));

            DB::commit();
            return $order->load(['items.product', 'items.variant']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Order $order, string $newStatus, ?string $notes = null, ?int $userId = null): Order
    {
        DB::beginTransaction();
        try {
            $oldStatus = $order->status;

            // Validate status transition
            $this->validateStatusTransition($oldStatus, $newStatus);

            // Update order status
            $order = $this->orderRepository->update($order, ['status' => $newStatus]);

            // Create status history
            $this->createStatusHistory($order, $newStatus, $notes, $userId);

            // Dispatch event
            event(new OrderStatusChanged($order, $oldStatus, $newStatus));

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order, string $reason, ?int $userId = null): Order
    {
        DB::beginTransaction();
        try {
            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled in current status');
            }

            // Restore inventory
            foreach ($order->items as $item) {
                $this->inventoryService->restoreStock(
                    $item->product,
                    $item->quantity,
                    $item->product_variant_id
                );
            }

            // Update order
            $order = $this->orderRepository->update($order, [
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Create status history
            $this->createStatusHistory($order, 'cancelled', $reason, $userId);

            // Dispatch event
            event(new OrderCancelled($order, $reason));

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate stock availability
     */
    protected function validateStockAvailability(array $items): void
    {
        foreach ($items as $item) {
            $product = $this->productRepository->findById($item['product_id']);

            if (!$product) {
                throw new \Exception("Product not found: {$item['product_id']}");
            }

            if (!$product->is_active) {
                throw new \Exception("Product is not available: {$product->name}");
            }

            if (isset($item['variant_id'])) {
                $variant = $product->variants()->find($item['variant_id']);
                if (!$variant || $variant->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for variant: {$product->name}");
                }
            } else {
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
            }
        }
    }

    /**
     * Calculate order totals
     */
    protected function calculateTotals(array $items, array $data): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $product = $this->productRepository->findById($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }

        $tax = $data['tax'] ?? 0;
        $shippingFee = $data['shipping_fee'] ?? 0;
        $discount = $data['discount'] ?? 0;
        $total = $subtotal + $tax + $shippingFee - $discount;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    /**
     * Create status history
     */
    protected function createStatusHistory(Order $order, string $status, ?string $notes, ?int $userId): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status,
            'notes' => $notes,
            'changed_by' => $userId,
        ]);
    }

    /**
     * Validate status transition
     */
    protected function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            throw new \Exception("Cannot transition from {$currentStatus} to {$newStatus}");
        }
    }
}