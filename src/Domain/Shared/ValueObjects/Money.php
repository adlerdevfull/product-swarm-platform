<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

/** Money stored in cents to avoid float precision issues. */
final readonly class Money
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new \DomainException('Money cannot be negative');
        }
    }

    public static function fromEuros(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->cents * $factor));
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function toEuros(): float
    {
        return $this->cents / 100;
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }
}
