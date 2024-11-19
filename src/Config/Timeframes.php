<?php
namespace App\Config;

enum Timeframes: int
{
    case TenMinutes = 0;
    case OneHour = 1;
    case OneDay = 2;
    case OneWeek = 3;
    case OneMonth = 4;
    case OneYear = 5;

    public function label(): string
    {
        return match($this) {
            self::TenMinutes => '10m',
            self::OneHour => '1h',
            self::OneDay => '1d',
            self::OneWeek => '1w',
            self::OneMonth => '1Mon',
            self::OneYear => '1y',
        };
    }

    public static function fromString(?string $label): ?self
    {
        return match($label) {
            '10m' => self::TenMinutes,
            '1h' => self::OneHour,
            '1d' => self::OneDay,
            '1w' => self::OneWeek,
            '1month' => self::OneMonth,
            '1year' => self::OneWeek,
            default => null,
        };
    }
}
