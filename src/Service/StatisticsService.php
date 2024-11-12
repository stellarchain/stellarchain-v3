<?php

namespace App\Service;

use App\Repository\MetricRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatisticsService
{
    public function __construct(
        private MetricRepository $metricsRepository,
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function getKeys(): array
    {
        return [
            'market-charts' => [
                'price-usd' => true,
                'rank' => true,
                'market-cap' => true,
                'volume-24h' => true,
                'circulating-supply' => true,
                'market-cap-dominance' => true,
                'total-trades' => false,
                'trades-volume' => false,
                'transactions-volume' => false,
                'cex-trade-volume' => false,
            ],
            'blockchain-charts' => [
                'blockchain-size' => true,
                'average-ledger-size' => false,
                'total-ledgers' => true,
                'failed-transactions' => true,
                'transactions-per-second' => true,
                'transactions-per-ledger' => true,
                'operations-per-ledger' => true,
                'number-of-transactions' => true,
                'number-of-operations' => true,
                'average-ledger-time' => true,
                'contract-invocations' => true,
                'created-contracts' => true,

            ],
            'network-charts' => [
                'total-accounts' => true,
                'total-assets' => true,
                'successful-transactions' => true,
                'active-addresses' => true,
                'inactive-addresses' => true,
                'top-100-active-addresses' => true,
                'output-value-per-day' => false,
                'transactions-value' => false,
            ],
            /* 'community_fund' => [ */
            /*     'total_submissions' => [], */
            /*     'total_awarded' => [], */
            /*     'activation_award_count' => [], */
            /*     'activation_awarded' => [], */
            /*     'community_award_count' => [], */
            /*     'community_awarded' => [], */
            /*     'voters' => [], */
            /* ], */
            /* 'stellarchain' => [ */
            /*     'total-users' => [], */
            /*     'communities' => [], */
            /*     'posts' => [], */
            /*     'likes' => [], */
            /*     'jobs' => [], */
            /*     'projects' => [], */
            /*     'visitors' => [], */
            /*     'page-views' => [], */
            /* ] */
        ];
    }

    /**
     * @return array<string,array>
     */
    public function getMetricsData(string $key, string $chartType,  string $timeframe, int $startTime, int $limit = 50): array
    {
        $metrics = $this->metricsRepository->findMetricsAfterTimestamp($key, $chartType, $timeframe, $startTime, $limit);
        $labels = array_map(fn ($metric) => $metric->getTimestamp(), $metrics);
        $data = array_map(fn ($metric) => round((float) $metric->getValue(), 5), $metrics);
        return [
            'labels' => array_reverse($labels),
            'data' => array_reverse($data)
        ];
    }

    /**
     * @return array<int,array>|array
     */
    public function buildStatisticsCharts(): array
    {
        $statistics = $this->getKeys();
        foreach ($statistics as $typeKey => $statisticKey) {
            foreach ($statisticKey as $key => $chart) {
                if ($chart) {
                    $metrics = $this->getMetricsData($key, $typeKey, '10m', time(), 100);
                    $change = 0;
                    $dataCount = count($metrics['data']);
                    if ($dataCount > 1) {
                        $latestValue = $metrics['data'][$dataCount - 1];
                        $previousValue = $metrics['data'][$dataCount - 2];
                        if ($previousValue != 0) {
                            $change = (($latestValue - $previousValue) / $previousValue) * 100;
                        }
                    }
                    $statistics[$typeKey][$key] = [
                        'chart' => $this->buildChart($metrics['labels'], $metrics['data']),
                        'change' => $change
                    ];
                }
            }
        }

        return $statistics;
    }

    /**
     * @param array<int,mixed> $labels
     * @param array<int,mixed> $data
     */
    private function buildChart(array $labels, array $data): Chart
    {
        $labels = array_map(fn($label) => $label->format('d M Y H:i'), $labels);
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'backgroundColor' => 'rgb(220, 53, 69)',
                    'borderColor' => 'rgb(220, 53, 69)',
                    'data' => $data,
                    'fill' => false,
                    'tension' => 0.1,
                    'borderWidth' => 3,
                    'pointBorderWidth' => 0.5,
                    'pointRadius' => 0.5,
                ],
            ],
        ]);
        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => true,
            'scales' => [
                'y' => ['display' => false],
                'x' => ['display' => false],
            ],
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'position' => 'average',
                    'intersect' => false,
                    'mode' => 'interpolate',
                ],
                'crosshair' => [
                    'zoom' => [
                        'enabled' => false,
                    ]
                ],
                'legend' => [
                    'display' => false,
                ],
                'zoom' => [
                    'zoom' => [
                        'wheel' => [
                            'enabled' => false
                        ],
                        'drag' => [
                            'enabled' => false
                        ],
                        'pinch' => [
                            'enabled' => false
                        ],
                        'mode' => 'x',
                    ],
                    'pan' => [
                        'enabled' => true,
                        'mode' => 'x'
                    ]
                ]
            ],
        ]);

        return $chart;
    }
}
