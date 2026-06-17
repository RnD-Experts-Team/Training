<?php

namespace App\Enums;

enum Importance: string
{
    case NotNecessary = 'not_necessary';
    case HighlyImportant = 'highly_important';
    case NeedsReview = 'needs_review';

    public function label(): string
    {
        return match ($this) {
            self::NotNecessary => 'Not Necessary',
            self::HighlyImportant => 'Highly Important',
            self::NeedsReview => 'Needs Review',
        };
    }

    /**
     * A semantic color key the frontend maps to badge styling.
     */
    public function color(): string
    {
        return match ($this) {
            self::NotNecessary => 'slate',
            self::HighlyImportant => 'red',
            self::NeedsReview => 'amber',
        };
    }
}
