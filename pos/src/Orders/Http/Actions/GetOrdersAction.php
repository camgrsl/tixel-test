<?php

declare(strict_types=1);

namespace Tixel\Orders\Http\Actions;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Tixel\Orders\Http\Resources\OrderResource;
use Tixel\Orders\Persistence\Repositories\OrderRepository;

class GetOrdersAction
{
    public function __invoke(OrderRepository $orderRepository): AnonymousResourceCollection
    {
        return OrderResource::collection($orderRepository->getAll());
    }
}
