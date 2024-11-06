<?php

namespace App\Service;

use App\Config\Timeframes;
use App\Repository\CoinStatRepository;
use App\Repository\MetricRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatisticsService
{
    public function __construct(
        private CoinStatRepository $coinStatRepository,
        private MetricRepository $metricsRepository,
        private NumberFormatter $numberFormatter,
        private ChartBuilderInterface $chartBuilder,
        private LedgerMetricsService $ledgerMetricsService,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function getKeys(): array
    {
        return [
            'market-charts' => [
                'price-usd' => [],
                'rank' => [],
                'market-cap' => [],
                'volume-24h' => [],
                'circulating-supply' => [],
                'market-cap-dominance' => [],
                'total-trades' => [],
                'trades-volume' => false,
                'transactions-volume' => false,
                'cex-trade-volume' => false,
            ],
            'blockchain-charts' => [
                'blockchain-size' => [],
                'average-ledger-size' => false,
                'total-ledgers' => [],
                'failed-transactions' => [],
                'transactions-per-second' => [],
                'transactions-per-ledger' => [],
                'operations-per-ledger' => [],
                'number-of-transactions' => [],
                'number-of-operations' => [],
                'average-ledger-time' => [],
                'contract-invocations' => [],
                'created-contracts' => [],

            ],
            'network-charts' => [
                'total-accounts' => [],
                'total-assets' => [],
                'successful-transactions' => [],
                'active-addresses' => [],
                'inactive-addresses' => [],
                'top-100-active-addresses' => [],
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
            /*     'total_users' => [], */
            /*     'communities' => [], */
            /*     'posts' => [], */
            /*     'likes' => [], */
            /*     'jobs' => [], */
            /*     'projects' => [], */
            /*     'visitors' => [], */
            /*     'page_views' => [], */
            /* ] */
        ];
    }

    public function getMetricsData(string $key, string $chartType,  string $timeframe, int $startTime): array
    {
        $metrics = $this->metricsRepository->findMetricsAfterTimestamp($key, $chartType, $timeframe, $startTime, 50);
        $labels = [];
        $data = [];
        foreach ($metrics as $metric) {
            $labels[] = $metric->getTimestamp();
            $data[] = round((float) $metric->getValue(), 5);
        }
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
                if (is_array($chart)) {
                    $metrics = $this->getMetricsData($key, $typeKey, '10m', time());
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

    private function buildChart(array $labels, array $data): Chart
    {
        foreach($labels as $k => $label){
            $labels[$k] = $label->format('d-m-Y H:i');
        }

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
