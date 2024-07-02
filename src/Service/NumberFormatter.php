<?php
namespace App\Service;

class NumberFormatter
{
    public function formatLargeNumber($number)
    {
        if ($number >= 1_000_000_000) {
            return number_format($number / 1_000_000_000, 2) . 'B';
        } elseif ($number >= 1_000_000) {
            return number_format($number / 1_000_000, 2) . 'M';
        } elseif ($number >= 1_000) {
            return number_format($number / 1_000, 2) . 'K';
        }

        return number_format($number, 2);
    }
}
