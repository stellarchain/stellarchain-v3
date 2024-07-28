<?php

namespace App\Twig\Components;

use App\Entity\CoinStat;
use App\Repository\CoinStatRepository;
use App\Service\NumberFormatter;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('market-information')]
final class MarketInformation
{
    use DefaultActionTrait;

    public function __construct(
        private CoinStatRepository $coinStatRepository,
        private NumberFormatter $numberFormatter
    ) {

        $this->coinStatRepository = $coinStatRepository;
    }

    /**
     * @return array<int,CoinStat>
     */
    public function getStats(): array
    {
        $requiredStats = ['rank', 'market_cap', 'volume_24h', 'price_usd', 'circulating_supply', 'market_cap_dominance'];
        $stellarCoinStats = $this->coinStatRepository->findLatestAndPreviousBySymbol('XLM', $requiredStats);
        $formattedStats = [];
        foreach ($stellarCoinStats as $stat) {
            $stat['change'] = 0;
            $stat['percentageChange'] = 0.00;
            $stat['caretDirection'] = 'right';
            $stat['color'] = 'secondary';

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
}
