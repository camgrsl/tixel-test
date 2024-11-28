<?php

declare(strict_types=1);

namespace Tixel\Orders\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Tixel\Orders\Events\UpdatedOrderStatus;
use Tixel\Orders\Listeners\SendOrderStatusUpdateWebhook;

class OrderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load custom bounded context route file
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/order.php',
            'order'
        );

        Event::listen(
            UpdatedOrderStatus::class,
            [SendOrderStatusUpdateWebhook::class, 'handle']
        );
    }

    public function register(): void
    {
        parent::register();
    }
}
