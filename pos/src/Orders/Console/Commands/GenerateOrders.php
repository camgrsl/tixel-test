<?php

declare(strict_types=1);

namespace Tixel\Orders\Console\Commands;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Console\Command;
use Tixel\Orders\Enum\OrderStatus;
use Tixel\Orders\Events\UpdatedOrderStatus;
use Tixel\Orders\Persistence\Models\Order;

class GenerateOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random orders';

    public function handle(BroadcastManager $broadcastManager): void
    {
        $min = 5;
        $max = 70;

        for ($i = 0; $i < 5; $i++) {
            $order = Order::create([
                'amount' => mt_rand($min * 10, $max * 10) / 10,
                'status' => OrderStatus::PLACED,
            ]);

            $broadcastManager->event(new UpdatedOrderStatus($order));
        }
    }
}
