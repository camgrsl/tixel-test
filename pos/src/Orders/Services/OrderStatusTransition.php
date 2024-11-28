<?php

declare(strict_types=1);

namespace Tixel\Orders\Services;

use Illuminate\Broadcasting\BroadcastManager;
use Tixel\Orders\Enum\OrderStatus;
use Tixel\Orders\Events\UpdatedOrderStatus;
use Tixel\Orders\Exceptions\OrderTransitionException;
use Tixel\Orders\Persistence\Models\Order;
use Tixel\Orders\Persistence\Repositories\OrderRepository;
use ValueError;

class OrderStatusTransition
{
    public function __construct(
        private BroadcastManager $broadcastManager,
        private OrderRepository $repository
    ) {}

    /**
     * @throws OrderTransitionException
     */
    public function transitionTo(Order $order, string $orderStatus): void
    {
        try {
            $orderStatus = OrderStatus::from($orderStatus);
        } catch (ValueError) {
            throw new OrderTransitionException('Invalid transition status');
        }

        // Match the desired order status with matching callback
        match ($orderStatus) {
            OrderStatus::PLACED => $this->placed($order),
            OrderStatus::COOKING => $this->cooking($order),
            OrderStatus::PREPARING => $this->preparing($order),
            OrderStatus::READY_FOR_DELIVERY => $this->readyForDelivery($order)
        };
    }

    private function placed(Order $order): void
    {
        throw new OrderTransitionException('Cannot revert order to its initial state');
    }

    private function preparing(Order $order): void
    {
        if ($order->status !== OrderStatus::PLACED) {
            throw new OrderTransitionException('Order must be `placed` in order to be prepared');
        }

        $this->updateStatus($order, OrderStatus::PREPARING);
    }

    private function cooking(Order $order): void
    {
        if ($order->status !== OrderStatus::PREPARING) {
            throw new OrderTransitionException('Order must be prepared in order to be cooked');
        }

        $this->updateStatus($order, OrderStatus::COOKING);
    }

    private function readyForDelivery(Order $order): void
    {
        if ($order->status !== OrderStatus::COOKING) {
            throw new OrderTransitionException('Order must be `cooking` in order to be ready of delivery');
        }

        $this->updateStatus($order, OrderStatus::READY_FOR_DELIVERY);
    }

    private function updateStatus(Order $order, OrderStatus $orderStatus): void
    {
        $order->status = $orderStatus->value;
        $this->repository->save($order);

        // Broadcast application event
        $this->broadcastManager->event(new UpdatedOrderStatus($order));
    }
}
