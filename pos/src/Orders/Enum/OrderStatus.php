<?php

declare(strict_types=1);

namespace Tixel\Orders\Enum;

enum OrderStatus: string
{
    case PLACED = 'placed';
    case PREPARING = 'preparing';
    case COOKING = 'cooking';
    case READY_FOR_DELIVERY = 'ready_for_delivery';
}
