<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Services\OrderService;
use App\Services\InvoiceService;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderRepository $orderRepository,
        protected InvoiceService $invoiceService
    ) {
    }

    /**
     * Get all orders
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $filters = $request->all();

        // Customers can only see their orders
        if ($user->isCustomer()) {
            $filters['user_id'] = $user->id;
        }

        // Vendors can only see orders with their products
        if ($user->isVendor()) {
            $orders = $this->orderRepository->getByVendor($user->id, $request->per_page ?? 15);
        } else {
            $orders = $this->orderRepository->getAll($filters, $request->per_page ?? 15);
        }

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get single order
     */
    public function show($id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $user = auth()->user();

        // Check access permissions
        if ($user->isCustomer() && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        if ($user->isVendor()) {
            // Check if vendor has products in this order
            $hasVendorProducts = $order->items->some(function ($item) use ($user) {
                return $item->product && $item->product->vendor_id === $user->id;
            });

            if (!$hasVendorProducts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Create order
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(
                $request->validated(),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update order status (Admin/Vendor only)
     */
    public function updateStatus(UpdateOrderStatusRequest $request, $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $user = auth()->user();

        // Vendors can only update orders with their products
        if ($user->isVendor()) {
            $hasVendorProducts = $order->items->some(function ($item) use ($user) {
                return $item->product && $item->product->vendor_id === $user->id;
            });

            if (!$hasVendorProducts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }
        }

        try {
            $order = $this->orderService->updateStatus(
                $order,
                $request->status,
                $request->notes,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(CancelOrderRequest $request, $id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $user = auth()->user();

        // Customers can only cancel their own orders
        if ($user->isCustomer() && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        try {
            $order = $this->orderService->cancelOrder(
                $order,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download invoice
     */
    public function downloadInvoice($id): mixed
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $user = auth()->user();

        // Check access permissions
        if ($user->isCustomer() && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $invoice = $order->invoices()->latest()->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        $filePath = $this->invoiceService->downloadInvoice($invoice);

        return response()->download($filePath, "{$order->order_number}-invoice.pdf");
    }

    /**
     * Get order statistics (Admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = [];

        if ($request->has('start_date') && $request->has('end_date')) {
            $filters['start_date'] = $request->start_date;
            $filters['end_date'] = $request->end_date;
        }

        $stats = $this->orderRepository->getStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get order status history
     */
    public function statusHistory($id): JsonResponse
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $user = auth()->user();

        // Check access permissions
        if ($user->isCustomer() && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $order->statusHistories()->with('changedBy')->orderBy('created_at')->get(),
        ]);
    }
}