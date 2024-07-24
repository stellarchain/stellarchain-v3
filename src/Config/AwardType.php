<?php

namespace App\Config;

enum AwardType: int
{
    case NotAssigned = 0;
    case ActivationAward = 1;
    case CommunityAward = 2;

    public function label(): string
    {
        return match($this) {
            self::ActivationAward => 'Activation Award',
            self::CommunityAward => 'Community Award',
            self::NotAssigned => 'Not Assigned',
        };
    }

    public static function fromString(?string $label): ?self
    {
        return match($label) {
            'Activation Award' => self::ActivationAward,
            'Community Award' => self::CommunityAward,
            default => self::NotAssigned,
        };
    }
}
