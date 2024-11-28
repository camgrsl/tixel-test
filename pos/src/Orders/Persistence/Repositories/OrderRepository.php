<?php

declare(strict_types=1);

namespace Tixel\Orders\Persistence\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Tixel\Orders\Persistence\Models\Order;

/**
 * This repository makes a little no sense considering the eloquent model is directly linked to it.
 * But still a good practice to have a persistence layer on top of the Eloquent ActiveRecord ORM.
 * This is a good practice to have model mutations on a dedicated class.
 * Best would be to have a whole domain layer with entities and services and avoid relying on Eloquent (outside of the repository).
 * But this would have taken a bit more time to implement.
 */
class OrderRepository
{
    public function findById(string $id): ?Order
    {
        return Order::find($id);
    }

    public function save(Order $order): bool
    {
        return $order->save();
    }

    public function getAll(): Collection
    {
        return Order::orderBy('id', 'DESC')->get();
    }
}
