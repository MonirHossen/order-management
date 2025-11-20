<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\OrderCancelled;
use App\Events\LowStockDetected;
use App\Listeners\SendOrderCreatedNotification;
use App\Listeners\SendOrderStatusChangedNotification;
use App\Listeners\SendOrderCancelledNotification;
use App\Listeners\SendLowStockNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Order Events
        OrderCreated::class => [
            SendOrderCreatedNotification::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusChangedNotification::class,
        ],
        OrderCancelled::class => [
            SendOrderCancelledNotification::class,
        ],
        
        // Inventory Events
        LowStockDetected::class => [
            SendLowStockNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}