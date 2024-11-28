<?php

use Tixel\Orders\Http\Actions\UpdateOrderStatusAction;
use Tixel\Orders\Http\Actions\WebhookConsumerExampleAction;
use Tixel\Orders\Http\Actions\GetOrdersAction;

Route::middleware('api')->group(function() {
    Route::patch('orders/{orderId}/', UpdateOrderStatusAction::class);
    Route::get('orders', GetOrdersAction::class);
});

// This is a test route to show off how webhook processing should work.
Route::post('/wh', WebhookConsumerExampleAction::class);
