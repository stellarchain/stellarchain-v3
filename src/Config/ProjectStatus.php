<?php

namespace App\Config;

enum ProjectStatus: int
{
    case Disabled = 0;
    case Inactive = 1;
    case Active = 2;
    case Review = 3;
    case Draft = 4;
    case Feasibility = 5;
    case Incubation = 6;
    case Evaluation = 7;

    public static function fromString(?string $label): ?self
    {
        return match($label) {
            'inactive' => self::Inactive,
            'active' => self::Active,
            'review' => self::Review,
            'draft' => self::Draft,
            'feasibility' => self::Feasibility,
            'incubation' => self::Incubation,
            'evaluation' => self::Evaluation,
            default => self::Disabled,
        };
    }
}
