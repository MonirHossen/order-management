<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Jobs\SendLowStockAlertJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLowStockNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        // Dispatch job to send email notification
        SendLowStockAlertJob::dispatch($event->product);
    }
}