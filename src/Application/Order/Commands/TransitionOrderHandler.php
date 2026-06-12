<?php

declare(strict_types=1);

namespace Application\Order\Commands;

use Domain\Order\Entities\Order;
use Domain\Order\Enums\OrderStatus;
use Domain\Order\Repositories\OrderRepositoryInterface;
use Domain\Product\Repositories\ProductRepositoryInterface;
use Infrastructure\Messaging\OrderEventPublisher;

final readonly class TransitionOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private ProductRepositoryInterface $products,
        private OrderEventPublisher $publisher,
    ) {}

    public function handle(int $orderId, OrderStatus $next): Order
    {
        $order = $this->orders->findById($orderId)
            ?? throw new \DomainException("Order not found: {$orderId}");

        $updated = $order->transition($next);

        if ($next === OrderStatus::Cancelled && $order->canBeCancelled()) {
            $product = $this->products->findById($order->productId);
            if ($product !== null) {
                $this->products->save($product->releaseStock($order->quantity));
            }
        }

        $saved = $this->orders->save($updated);
        $this->publisher->orderStatusChanged($saved);

        return $saved;
    }
}
