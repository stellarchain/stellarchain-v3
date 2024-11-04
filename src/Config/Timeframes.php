<?php
namespace App\Config;

enum Timeframes: int
{
    case FiveMinutes = 0;
    case OneHour = 1;
    case OneDay = 2;
    case OneWeek = 3;

    public function label(): string
    {
        return match($this) {
            self::FiveMinutes=> '5m',
            self::OneHour => '1h',
            self::OneDay => '1d',
            self::OneWeek => '1w',
        };
    }

    public static function fromString(?string $label): ?self
    {
        return match($label) {
            '5m' => self::FiveMinutes,
            '1h' => self::OneHour,
            '1d' => self::OneDay,
            '1w' => self::OneWeek,
        };
    }
}
