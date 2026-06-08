<?php

declare(strict_types=1);

namespace Domain\Product\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Draft => in_array($next, [self::Published, self::Archived], true),
            self::Published => in_array($next, [self::Draft, self::Archived], true),
            self::Archived => false,
        };
    }
}
