<?php

declare(strict_types=1);

namespace Tixel\Orders\Http\Actions;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Rfc4122\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tixel\Orders\Exceptions\OrderTransitionException;
use Tixel\Orders\Http\Resources\OrderResource;
use Tixel\Orders\Persistence\Repositories\OrderRepository;
use Tixel\Orders\Services\OrderStatusTransition;

readonly class UpdateOrderStatusAction
{
    public function __construct(
        private OrderStatusTransition $orderStatusTransition,
        private OrderRepository $orderRepository,
        private Validator $validator,
    ) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request, string $orderId): OrderResource
    {
        // Validate that the request parameter is a valid UUIDs of the RFC 4122 variant.
        if (! $this->validator->validate($orderId)) {
            throw new BadRequestHttpException;
        }

        if (null === $order = $this->orderRepository->findById($orderId)) {
            throw new NotFoundHttpException;
        }

        // Validate request body
        $validated = $request->validate([
            'status' => 'required',
        ]);

        // Status validation responsibility is under OrderTransitionClass
        // Sso it's agnostic from the transport layer
        // Validation exception is manually triggered to follow Laravel 422 response status code standard
        try {
            $this->orderStatusTransition->transitionTo($order, $validated['status']);
        } catch (OrderTransitionException $orderTransitionException) {
            throw ValidationException::withMessages(['status' => $orderTransitionException->getMessage()]);
        }

        return new OrderResource($order);
    }
}
