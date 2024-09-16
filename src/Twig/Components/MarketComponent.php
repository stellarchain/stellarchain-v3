<?php

namespace App\Twig\Components;

use App\Repository\StellarHorizon\AssetMetricRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Repository\StellarHorizon\AssetRepository;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent('market-component')]
final class MarketComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $currency = 'USD'; // Default currency

    #[LiveProp(writable: true, url: true)]
    public string $range = '24 hour'; // Default time range

    #[LiveProp(writable: true, url: true)]
    public string $sort = 'default'; // Default sorting

    #[LiveProp(writable: true)]
    public int $page = 1;

    private const PER_PAGE = 20;

    public function __construct(
        public AssetRepository $assetRepository,
        public AssetMetricRepository $assetMetricRepository,
        public ChartBuilderInterface $chartBuilder
    ) {
    }

    #[LiveAction]
    public function resetPage(): void
    {
        $this->page = 1;
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        $totalAssets = $this->assetRepository->count(['in_market' => true]);
        return $totalAssets > ($this->page * self::PER_PAGE);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    public function getAssetsData(): array
    {
        $filterCriteria = $this->buildFilterCriteria();
        $sortCriteria = $this->buildSortCriteria();
        $offset = ($this->page - 1) * self::PER_PAGE;

        $assets = $this->assetRepository->findBy(
            $filterCriteria,
            $sortCriteria,
            self::PER_PAGE,
            $offset
        );
        $assetsData = [];
        foreach ($assets as $asset) {
            $latestMetric = $this->assetMetricRepository->findOneBy(
                ['asset' => $asset],
                ['created_at' => 'DESC']
            );

            if ($latestMetric) {
                $recentMetrics = $this->assetMetricRepository->findBy(
                    ['asset' => $asset],
                    ['created_at' => 'DESC'],
                    5
                );
                $metricsForChart = array_reverse($recentMetrics);

                $chartData = array_map(fn ($metric) => $metric->getPriceChange1h(), $metricsForChart);
                $chartLabels = array_map(fn ($metric) => $metric->getCreatedAt()->format('H:i'), $metricsForChart);

                $chart = $this->buildChart($chartLabels, $chartData);

                $assetsData[] = [
                    'asset' => $asset,
                    'latestMetric' => $latestMetric,
                    'chart' => $chart,
                ];
            }
        }

        if ($this->sort == 'default'){
            usort($assetsData, fn ($a, $b) => $b['latestMetric']->getPrice() <=> $a['latestMetric']->getPrice());
        }

        return [
            'assets' => $assetsData, // Precomputed asset data
        ];
    }

    /**
     * @return array<string,string>
     */
    private function buildSortCriteria(): array
    {
        switch ($this->sort) {
            case 'volume':
                return ['amount' => 'DESC'];
            case 'trustlines':
                return ['amount' => 'DESC'];
            case 'cap':
                return ['amount' => 'DESC'];
            case 'age':
                return ['created_at' => 'ASC'];
            default:
                return [];
        }
    }

    private function buildFilterCriteria(): array
    {
        return ['in_market' => true];
    }
    /**
     * Helper function to build the chart object
     */
    private function buildChart(array $labels, array $data): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $data,
                    'fill' => false,
                    'tension' => 0.1,
                    'borderWidth' => 1,
                    'pointBorderWidth' => 0.2,
                    'pointRadius' => 1.5,
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => ['display' => false],
                'x' => ['display' => false],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ]);

        return $chart;
    }
}
