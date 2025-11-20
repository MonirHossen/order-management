<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        try {
            Mail::send('emails.order-confirmation', [
                'order' => $this->order,
            ], function ($message) {
                $message->to($this->order->shipping_email)
                    ->subject('Order Confirmation - ' . $this->order->order_number);
            });

            Log::info('Order confirmation email sent', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

class SendOrderStatusUpdateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $newStatus
    ) {
    }

    public function handle(): void
    {
        try {
            Mail::send('emails.order-status-update', [
                'order' => $this->order,
                'status' => $this->newStatus,
            ], function ($message) {
                $message->to($this->order->shipping_email)
                    ->subject('Order Status Update - ' . $this->order->order_number);
            });

            Log::info('Order status update email sent', [
                'order_id' => $this->order->id,
                'status' => $this->newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order status update email', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

class SendOrderCancellationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $reason
    ) {
    }

    public function handle(): void
    {
        try {
            Mail::send('emails.order-cancellation', [
                'order' => $this->order,
                'reason' => $this->reason,
            ], function ($message) {
                $message->to($this->order->shipping_email)
                    ->subject('Order Cancelled - ' . $this->order->order_number);
            });

            Log::info('Order cancellation email sent', [
                'order_id' => $this->order->id,
                'reason' => $this->reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order cancellation email', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}