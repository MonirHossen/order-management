<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\OrderCancelled;
use App\Jobs\SendOrderConfirmationEmail;
use App\Jobs\SendOrderStatusUpdateEmail;
use App\Jobs\SendOrderCancellationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderCreatedNotification implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        SendOrderConfirmationEmail::dispatch($event->order);
    }
}

class SendOrderStatusChangedNotification implements ShouldQueue
{
    public function handle(OrderStatusChanged $event): void
    {
        SendOrderStatusUpdateEmail::dispatch($event->order, $event->newStatus);
    }
}

class SendOrderCancelledNotification implements ShouldQueue
{
    public function handle(OrderCancelled $event): void
    {
        SendOrderCancellationEmail::dispatch($event->order, $event->reason);
    }
}