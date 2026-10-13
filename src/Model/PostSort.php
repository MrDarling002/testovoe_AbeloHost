<?php

declare(strict_types=1);

namespace App\Model;

enum PostSort: string
{
    case Date  = 'date';
    case Views = 'views';

    public function orderBy(): string
    {
        return match ($this) {
            self::Date  => 'p.published_at DESC',
            self::Views => 'p.views_count DESC',
        };
    }

    public static function fromRequestValue(mixed $value): self
    {
        if (!is_string($value)) {
            return self::Date;
        }

        return self::tryFrom($value) ?? self::Date;
    }
}