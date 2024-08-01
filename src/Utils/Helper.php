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
            echo $e->getMessage().PHP_EOL;
            return false;
        }
    }
}
