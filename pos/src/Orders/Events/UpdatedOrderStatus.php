<?php

declare(strict_types=1);

namespace Tixel\Orders\Events;

use Tixel\Orders\Persistence\Models\Order;

class UpdatedOrderStatus
{
    public function __construct(public Order $order) {}
}
