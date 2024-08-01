<?php

namespace App\Twig\Components;

use App\Entity\CoinStat;
use App\Repository\CoinStatRepository;
use App\Service\MarketDataService;
use App\Service\NumberFormatter;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('market-information')]
final class MarketInformation
{
    use DefaultActionTrait;

    public function __construct(
        private CoinStatRepository $coinStatRepository,
        private NumberFormatter $numberFormatter,
        private MarketDataService $marketDataService
    ) {

        $this->coinStatRepository = $coinStatRepository;
        $this->marketDataService = $marketDataService;
    }

    /**
     * @return array<int,CoinStat>
     */
    public function getStats(): array
    {
        return $this->marketDataService->buildMarketOverview();
    }
}
