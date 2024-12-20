<?php

namespace App\Service;

use App\Repository\CoinStatRepository;

class MarketDataService
{
    private $coinStatRepository;
    private $numberFormatter;

    public function __construct(CoinStatRepository $coinStatRepository, NumberFormatter $numberFormatter)
    {
        $this->coinStatRepository = $coinStatRepository;
        $this->numberFormatter = $numberFormatter;
    }

    public function buildMarketOverview(): array
    {
        $requiredStats = ['rank', 'market-cap', 'volume-24h', 'price-usd', 'circulating-supply', 'market-cap-dominance'];
        $stellarCoinStats = $this->coinStatRepository->findLatestAndPreviousBySymbol('XLM', $requiredStats);
        $formattedStats = [];

        foreach ($stellarCoinStats as $stat) {
            $stat['change'] = 0;
            $stat['percentageChange'] = 0.00;
            $stat['caretDirection'] = 'right';
            $stat['color'] = 'secondary';
            $stat['displayChange'] = '0';

            if ($stat['value'] !== null && $stat['prev_value'] !== null) {
                $stat['change'] =  $stat['value'] - $stat['prev_value'];
            }
            if ($stat['prev_value'] !== null && $stat['prev_value'] != 0) {
                $stat['percentageChange'] = number_format(($stat['change'] / $stat['prev_value']) * 100, 2);
            }

            $stat['caretDirection'] = $stat['change'] < 0 ? 'down' : 'up';
            $stat['color'] = $stat['change'] < 0 ? 'danger' : 'success';

            if ($stat['percentageChange'] == 0.00) {
                $stat['color'] = 'secondary';
                $stat['caretDirection'] = 'right';
            }

            $stat['value'] = $this->numberFormatter->formatLargeNumber($stat['value']);
            $stat['prev_value'] = $this->numberFormatter->formatLargeNumber($stat['prev_value']);

            $formattedStats[$stat['name']] = $stat;
        }

        return $formattedStats;
    }

    public function calculatePriceChange(float $latestPrice, ?float $previousPrice): ?float
    {
        if ($previousPrice === null || $previousPrice == 0) {
            return null;
        }
        $change = (($latestPrice - $previousPrice) / $previousPrice) * 100;
        return round($change, 2);
    }

    public function calculateAssetRank($latestPrice, $volume24hInUsd, $totalTrades)
    {
        $wPrice = 0.01;  // 10% weight for price
        $wVolume = 0.1; // 20% weight for volume
        $wTrades = 0.8; // 10% weight for total trades

        // Raw values (Price is inverted to match the previous logic)
        $priceScore = 1 / $latestPrice;
        $totalTradesScore = $totalTrades;

        // Final rank score calculation using the weighted sum of the raw values
        $rankScore = ($wPrice * $priceScore) +
            ($wVolume * $volume24hInUsd) +
            ($wTrades * $totalTradesScore);

        return number_format($rankScore, 5, '.', '');
    }
}
