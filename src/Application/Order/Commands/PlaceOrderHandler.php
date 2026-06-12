<?php

declare(strict_types=1);

namespace Application\Order\Commands;

use Domain\Order\Entities\Order;
use Domain\Order\Repositories\OrderRepositoryInterface;
use Domain\Product\Repositories\ProductRepositoryInterface;
use Infrastructure\Messaging\OrderEventPublisher;

final readonly class PlaceOrderHandler
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private OrderRepositoryInterface $orders,
        private OrderEventPublisher $publisher,
    ) {}

    public function handle(int $userId, int $productId, int $quantity): Order
    {
        $product = $this->products->findById($productId)
            ?? throw new \DomainException("Product not found: {$productId}");

        $reserved = $product->reserveStock($quantity);
        $this->products->save($reserved);

        $order = Order::place(
            userId: $userId,
            productId: $product->id ?? $productId,
            productSku: $product->sku,
            quantity: $quantity,
            unitPrice: $product->price,
        );

        $saved = $this->orders->save($order);
        $this->publisher->orderPlaced($saved);

        return $saved;
    }
}
