<?php

declare(strict_types=1);

namespace Domain\Product\Entities;

use Domain\Product\Enums\ProductStatus;
use Domain\Shared\ValueObjects\Money;

class Product
{
    private function __construct(
        public readonly ?int $id,
        public readonly string $sku,
        public readonly string $name,
        public readonly string $description,
        public readonly Money $price,
        public int $stock,
        public ProductStatus $status,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        string $sku,
        string $name,
        string $description,
        Money $price,
        int $stock,
    ): self {
        if ($sku === '' || $name === '') {
            throw new \DomainException('SKU and name are required');
        }
        if ($stock < 0) {
            throw new \DomainException('Stock cannot be negative');
        }

        return new self(
            id: null,
            sku: strtoupper(trim($sku)),
            name: trim($name),
            description: $description,
            price: $price,
            stock: $stock,
            status: ProductStatus::Draft,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        string $sku,
        string $name,
        string $description,
        Money $price,
        int $stock,
        ProductStatus $status,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $sku, $name, $description, $price, $stock, $status, $createdAt);
    }

    public function publish(): self
    {
        return $this->transition(ProductStatus::Published);
    }

    public function archive(): self
    {
        return $this->transition(ProductStatus::Archived);
    }

    public function transition(ProductStatus $next): self
    {
        if (!$this->status->canTransitionTo($next)) {
            throw new \DomainException(
                "Cannot transition product from {$this->status->value} to {$next->value}"
            );
        }

        $clone = clone $this;
        $clone->status = $next;
        return $clone;
    }

    public function reserveStock(int $quantity): self
    {
        if ($this->status !== ProductStatus::Published) {
            throw new \DomainException('Only published products can be ordered');
        }
        if ($quantity <= 0) {
            throw new \DomainException('Quantity must be positive');
        }
        if ($this->stock < $quantity) {
            throw new \DomainException("Insufficient stock for SKU {$this->sku}");
        }

        $clone = clone $this;
        $clone->stock -= $quantity;
        return $clone;
    }

    public function releaseStock(int $quantity): self
    {
        if ($quantity <= 0) {
            throw new \DomainException('Quantity must be positive');
        }

        $clone = clone $this;
        $clone->stock += $quantity;
        return $clone;
    }

    public function isAvailable(int $quantity = 1): bool
    {
        return $this->status === ProductStatus::Published && $this->stock >= $quantity;
    }
}
