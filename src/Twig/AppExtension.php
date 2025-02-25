<?php

namespace App\Twig;

use App\Config\AwardType;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Service\NumberFormatter;

class AppExtension extends AbstractExtension
{
    private $numberFormatter;

    public function __construct(NumberFormatter $numberFormatter)
    {
        $this->numberFormatter = $numberFormatter;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
            new TwigFilter('format_large_number', [$this, 'formatLargeNumber']),
            new TwigFilter('unicode_decode', [$this, 'unicodeDecode']),
            new TwigFilter('award_label', [$this, 'getAwardLabel']),
            new TwigFilter('get_initials', [$this, 'getInitials']),
        ];
    }

    public function formatPrice(float $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$' . $price;

        return $price;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('random_int', [$this, 'randomInt']),
        ];
    }

    public function randomInt(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public function formatLargeNumber($number)
    {
        return $this->numberFormatter->formatLargeNumber($number);
    }

    public function unicodeDecode($string)
    {
        return json_decode('"' . $string . '"');
    }

    public function getAwardLabel(?AwardType $awardType): string
    {
        return $awardType ? $awardType->label() : 'Not Assigned';
    }

    /**
     * Generate initials from the project name.
     */
    public function getInitials(string $projectName): string
    {
        $words = preg_split('/[\s\.\-]+/', preg_replace('/[^a-zA-Z\s]/', '', $projectName));

        // Get the first letter of the first word
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }

        // Limit initials to one character (e.g., for "Ziriz (Phase 2)" -> "Z")
        return substr($initials, 0, 2);
    }
}
