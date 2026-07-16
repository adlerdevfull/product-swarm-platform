<?php

declare(strict_types=1);

namespace Tests\Unit;

use Domain\Product\Entities\Product;
use Domain\Product\Enums\ProductStatus;
use Domain\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function test_create_starts_as_draft(): void
    {
        $p = Product::create('sku-1', 'API', 'desc', new Money(1000), 10);
        $this->assertSame(ProductStatus::Draft, $p->status);
        $this->assertSame('SKU-1', $p->sku);
    }

    public function test_publish_and_reserve_stock(): void
    {
        $p = Product::create('SKU-2', 'Pack', 'desc', new Money(500), 5)->publish();
        $reserved = $p->reserveStock(2);
        $this->assertSame(3, $reserved->stock);
    }

    public function test_cannot_order_draft(): void
    {
        $p = Product::create('SKU-3', 'X', 'd', new Money(100), 5);
        $this->expectException(\DomainException::class);
        $p->reserveStock(1);
    }

    public function test_insufficient_stock(): void
    {
        $p = Product::create('SKU-4', 'X', 'd', new Money(100), 1)->publish();
        $this->expectException(\DomainException::class);
        $p->reserveStock(5);
    }

    public function test_invalid_transition(): void
    {
        $p = Product::create('SKU-5', 'X', 'd', new Money(100), 1)->archive();
        $this->expectException(\DomainException::class);
        $p->publish();
    }
}
