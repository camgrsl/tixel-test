<?php

declare(strict_types=1);

namespace Tixel\Orders\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Tixel\Orders\Enum\OrderStatus;

class Order extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'status' => OrderStatus::class,
    ];

    protected $primaryKey = 'id';
}
