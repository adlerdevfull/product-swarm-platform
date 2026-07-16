<?php

declare(strict_types=1);

namespace Tests\Unit;

use Domain\Order\Entities\Order;
use Domain\Order\Enums\OrderStatus;
use Domain\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function test_place_calculates_total(): void
    {
        $o = Order::place(1, 10, 'SKU-A', 3, new Money(1000));
        $this->assertSame(OrderStatus::Pending, $o->status);
        $this->assertSame(3000, $o->total->cents);
        $this->assertStringStartsWith('ORD-', $o->orderNumber);
    }

    public function test_happy_path_lifecycle(): void
    {
        $o = Order::place(1, 10, 'SKU-A', 1, new Money(500));
        $o = $o->confirm()->ship()->deliver();
        $this->assertSame(OrderStatus::Delivered, $o->status);
    }

    public function test_cannot_ship_from_pending(): void
    {
        $o = Order::place(1, 10, 'SKU-A', 1, new Money(500));
        $this->expectException(\DomainException::class);
        $o->ship();
    }

    public function test_cancel_from_confirmed(): void
    {
        $o = Order::place(1, 10, 'SKU-A', 1, new Money(500))->confirm()->cancel();
        $this->assertSame(OrderStatus::Cancelled, $o->status);
    }
}
