<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function canTransitionTo(self $targetStatus): bool
    {
        if ($this === $targetStatus) {
            return true;
        }

        return match ($this) {
            self::DRAFT => $targetStatus === self::PUBLISHED || $targetStatus === self::ARCHIVED,
            self::PUBLISHED => $targetStatus === self::ARCHIVED,
            self::ARCHIVED => false,
        };
    }
}
