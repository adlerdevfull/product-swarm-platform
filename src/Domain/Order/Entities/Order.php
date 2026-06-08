<?php

declare(strict_types=1);

namespace Domain\Order\Entities;

use Domain\Order\Enums\OrderStatus;
use Domain\Shared\ValueObjects\Money;

class Order
{
    private function __construct(
        public readonly ?int $id,
        public readonly string $orderNumber,
        public readonly int $userId,
        public readonly int $productId,
        public readonly string $productSku,
        public readonly int $quantity,
        public readonly Money $unitPrice,
        public readonly Money $total,
        public OrderStatus $status,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public static function place(
        int $userId,
        int $productId,
        string $productSku,
        int $quantity,
        Money $unitPrice,
    ): self {
        if ($quantity <= 0) {
            throw new \DomainException('Quantity must be positive');
        }

        return new self(
            id: null,
            orderNumber: self::generateNumber(),
            userId: $userId,
            productId: $productId,
            productSku: $productSku,
            quantity: $quantity,
            unitPrice: $unitPrice,
            total: new Money($unitPrice->cents * $quantity),
            status: OrderStatus::Pending,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        string $orderNumber,
        int $userId,
        int $productId,
        string $productSku,
        int $quantity,
        Money $unitPrice,
        Money $total,
        OrderStatus $status,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id, $orderNumber, $userId, $productId, $productSku,
            $quantity, $unitPrice, $total, $status, $createdAt,
        );
    }

    public function confirm(): self
    {
        return $this->transition(OrderStatus::Confirmed);
    }

    public function ship(): self
    {
        return $this->transition(OrderStatus::Shipped);
    }

    public function deliver(): self
    {
        return $this->transition(OrderStatus::Delivered);
    }

    public function cancel(): self
    {
        return $this->transition(OrderStatus::Cancelled);
    }

    public function transition(OrderStatus $next): self
    {
        if (!$this->status->canTransitionTo($next)) {
            throw new \DomainException(
                "Cannot transition order from {$this->status->value} to {$next->value}"
            );
        }

        $clone = clone $this;
        $clone->status = $next;
        return $clone;
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canTransitionTo(OrderStatus::Cancelled);
    }

    private static function generateNumber(): string
    {
        return 'ORD-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 10));
    }
}
