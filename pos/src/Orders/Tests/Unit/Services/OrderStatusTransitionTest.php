<?php

declare(strict_types=1);

namespace Tixel\Orders\Tests\Services;

use Illuminate\Broadcasting\BroadcastManager;
use PHPUnit\Framework\TestCase;
use Tixel\Orders\Enum\OrderStatus;
use Tixel\Orders\Exceptions\OrderTransitionException;
use Tixel\Orders\Persistence\Models\Order;
use Tixel\Orders\Persistence\Repositories\OrderRepository;
use Tixel\Orders\Services\OrderStatusTransition;

class OrderStatusTransitionTest extends TestCase
{
    private OrderRepository $repository;
    private BroadcastManager $broadcastManager;
    private OrderStatusTransition $service;
    private Order $order;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(OrderRepository::class);
        $this->broadcastManager = $this->createMock(BroadcastManager::class);
        $this->service = new OrderStatusTransition(
            $this->broadcastManager,
            $this->repository
        );
        $this->order = new Order();
    }

    public function testInitialPlacedStatusTransitionThrowsException(): void
    {
        $this->expectException(OrderTransitionException::class);
        $this->expectExceptionMessage('Cannot revert order to its initial state');

        $this->service->transitionTo($this->order, OrderStatus::PLACED->value);
    }

    public function testPreparingTransitionFromInvalidStatusThrowsException(): void
    {
        $this->order->status = OrderStatus::COOKING->value;

        $this->expectException(OrderTransitionException::class);
        $this->expectExceptionMessage('Order must be `placed` in order to be prepared');

        $this->service->transitionTo($this->order, OrderStatus::PREPARING->value);
    }

    public function testCookingTransitionFromInvalidStatusThrowsException(): void
    {
        $this->order->status = OrderStatus::PLACED->value;

        $this->expectException(OrderTransitionException::class);
        $this->expectExceptionMessage('Order must be prepared in order to be cooked');

        $this->service->transitionTo($this->order, OrderStatus::COOKING->value);
    }

    public function testReadyForDeliveryTransitionFromInvalidStatusThrowsException(): void
    {
        $this->order->status = OrderStatus::PREPARING->value;

        $this->expectException(OrderTransitionException::class);
        $this->expectExceptionMessage('Order must be `cooking` in order to be ready of delivery');

        $this->service->transitionTo($this->order, OrderStatus::READY_FOR_DELIVERY->value);
    }

    public function testInvalidStatusTransitionThrowsException(): void
    {
        $this->expectException(OrderTransitionException::class);
        $this->expectExceptionMessage('Invalid transition status');

        $this->service->transitionTo($this->order, 'INVALID_STATUS');
    }
}
