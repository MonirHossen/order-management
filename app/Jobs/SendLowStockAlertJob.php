<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLowStockAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get vendor/admin emails
            $recipients = [];

            // Add vendor email
            if ($this->product->vendor) {
                $recipients[] = $this->product->vendor->email;
            }

            // Add admin emails
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $recipients[] = $admin->email;
            }

            // Send email notification
            foreach (array_unique($recipients) as $email) {
                Mail::send('emails.low-stock-alert', [
                    'product' => $this->product,
                ], function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Low Stock Alert: ' . $this->product->name);
                });
            }

            Log::info('Low stock alert sent', [
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'stock_quantity' => $this->product->stock_quantity,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send low stock alert', [
                'product_id' => $this->product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}