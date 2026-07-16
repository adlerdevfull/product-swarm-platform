<?php

declare(strict_types=1);

namespace Tests\Unit;

use Domain\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_rejects_negative(): void
    {
        $this->expectException(\DomainException::class);
        new Money(-1);
    }

    public function test_add_and_multiply(): void
    {
        $a = new Money(100);
        $b = $a->add(new Money(50));
        $this->assertSame(150, $b->cents);
        $this->assertSame(200, $a->multiply(2.0)->cents);
    }

    public function test_from_euros(): void
    {
        $m = Money::fromEuros(19.99);
        $this->assertSame(1999, $m->cents);
        $this->assertEqualsWithDelta(19.99, $m->toEuros(), 0.001);
    }
}
