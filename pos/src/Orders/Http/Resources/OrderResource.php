<?php

declare(strict_types=1);

namespace Tixel\Orders\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tixel\Orders\Enum\OrderStatus;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'amount' => $this->resource->amount,
            'status' => $this->resource->status,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'transition' =>  match($this->resource->status) {
                OrderStatus::PLACED => OrderStatus::PREPARING,
                OrderStatus::PREPARING => OrderStatus::COOKING,
                OrderStatus::COOKING => OrderStatus::READY_FOR_DELIVERY,
                OrderStatus::READY_FOR_DELIVERY => null,
            }
        ];
    }
}
