<?php

namespace App\Utils;

use DateTimeZone;
use DateTimeImmutable;
use Exception;


class Helper
{
    public function arrayToDateTimeImmutable(?array $dateTimeArray): ?DateTimeImmutable
    {
        if ($dateTimeArray) {
            $date = $dateTimeArray['date'];
            $timezone = new DateTimeZone($dateTimeArray['timezone']);

            return new DateTimeImmutable($date, $timezone);
        }
        return null;
    }

    public function normalizeString(string $input): string
    {
        $lowercaseString = strtolower($input);
        $normalizedString = str_replace(' ', '-', $lowercaseString);
        return $normalizedString;
    }

    public function downloadImage(string $url): string|bool
    {
        $tempDirectory = sys_get_temp_dir();
        $tempFilePath = $tempDirectory . DIRECTORY_SEPARATOR . uniqid('image_', true) . '.jpg';
        try {
            $imageContent = file_get_contents($url);
            if ($imageContent === false) {
                throw new Exception("Failed to download image.");
            }
            file_put_contents($tempFilePath, $imageContent);

            return $tempFilePath;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return false;
        }
    }

    public function getUrlParams(string $url): array
    {
        $query_string = parse_url($url, PHP_URL_QUERY);
        $query_params = [];
        parse_str($query_string, $query_params);

        return $query_params;
    }

    public function stellar_price(string $amount): string
    {
        return number_format($amount / 10000000, 7, '.', '');
    }

    public function takeInterval(string $label): string
    {
        $interval = strtoupper($label);
        if (strpos($interval, 'Y') !== false) {
            $interval = 'P' . str_replace('Y', 'Y', $interval);
        }elseif (strpos($interval, 'W') !== false) {
            $interval = 'P' . str_replace('W', 'W', $interval);
        } elseif (strpos($interval, 'D') !== false) {
            $interval = 'P' . str_replace('D', 'D', $interval);
        } elseif (strpos($interval, 'H') !== false) {
            $interval = 'PT' . str_replace('H', 'H', $interval);
        } elseif (strpos($interval, 'M') !== false) {
            $interval = 'PT' . str_replace('M', 'M', $interval);
        } else {
            return false;
        }

        return $interval;
    }
}
