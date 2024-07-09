<?php

namespace App\Twig\Components;

use App\Entity\CoinStat;
use App\Repository\CoinStatRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('market-information')]
final class MarketInformation
{
    use DefaultActionTrait;

    private $coinStatRepository;

    public function __construct(CoinStatRepository $coinStatRepository)
    {

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
            $formattedStats[$stat['name']] = $stat;
        }

        return $formattedStats;
    }
}
