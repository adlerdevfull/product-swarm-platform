<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => in_array($next, [self::Shipped, self::Cancelled], true),
            self::Shipped => $next === self::Delivered,
            self::Delivered, self::Cancelled => false,
        };
    }
}
