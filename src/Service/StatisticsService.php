<?php

namespace App\Service;

use App\Repository\CoinStatRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatisticsService
{
    public function __construct(
        private CoinStatRepository $coinStatRepository,
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
            'price_charts' => [
                'price_usd' => [],
                'market_cap' => [],
                'volume_24h' => [],
                'circulating_supply' => [],
                'market_cap_dominance' => [],
                'trades_volume' => false,
                'transactions_volume' => false,
                'cex_trade_volume' => false,
            ],
            'blockchain_charts' => [
                'blockchain_size' => false,
                'average_ledger_size' => false,
                'ledger_per_day' => false,
                'transactions_per_ledger' => [],
                'successful_transactions' => [],
                'failed_transactions' => [],
                'transactions_per_second' => [],
                'operations_per_ledger' => [],
                'number_of_transactions' => [],
                'average_ledger_time' => [],
                'contract_invocations' => [],
                'created_contracts' => [],
            ],
            //todo
            'network_charts' => [
                'total_assets' => [],
                /*     'active_addresses' => [], */
                /*     'inactive_addresses' => [], */
                /*     'top_100_active_addresses' => [], */
                /*     'output_value_per_day' => [], */
                /*     'transactions_value' => [], */
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

    public function getMetricsData($type, $stat)
    {
        $metrics = [
            'label' => [],
            'data' => [],
            'change' => 0
        ];
        if ($type == 'price_charts') {
            $metrics = $this->getPriceMetrics($stat);
        }

        if ($type == 'blockchain_charts') {
            $metrics = $this->getBlockchainMetrics($stat);
        }

        return $metrics;
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
                    $metrics = $this->getMetricsData($typeKey, $key);
                    $statistics[$typeKey][$key] = [
                        'chart' => $this->buildChart($metrics['label'], $metrics['data']),
                        'change' => $metrics['change']
                    ];
                }
            }
        }
        return $statistics;
    }

    public function getBlockchainMetrics($key)
    {
        $endDate = new \DateTimeImmutable();
        $startDate = $endDate->sub(new \DateInterval('PT5H'));
        $ledgerMetrics = $this->ledgerMetricsService->getMetricsForTimeIntervals($startDate, $endDate, 1, 100);

        $labels = [];
        $data = [];
        foreach ($ledgerMetrics as $entry) {
            $labels[] = $entry['time_start'];
            $data[] = $entry[$key];
        }
        $change = 0;
        $dataCount = count($data);

        if ($dataCount > 1) {
            $latestValue = $data[$dataCount - 1];
            $previousValue = $data[$dataCount - 2];

            if ($previousValue != 0) {
                $change = (($latestValue - $previousValue) / $previousValue) * 100;
            }
        }

        return [
            'change' => $change,
            'label' => $labels,
            'data' => $data
        ];
    }

    public function getPriceMetrics(string $key): array
    {
        $pageData = $this->coinStatRepository->getStatsByName($key, 0, 50);
        $priceData = array_reverse($pageData);
        $labels = [];
        $data = [];
        foreach ($priceData as $entry) {
            $labels[] = \DateTime::createFromFormat('Y-m-d H:i:s', $entry['created_at'])->format('m-d-Y H:i');
            $data[] = (float) $entry['value'];
        }

        $change = 0;
        $dataCount = count($data);

        if ($dataCount > 1) {
            $latestValue = $data[$dataCount - 1];
            $previousValue = $data[$dataCount - 2];

            if ($previousValue != 0) {
                $change = (($latestValue - $previousValue) / $previousValue) * 100;
            }
        }

        return [
            'change' => $change,
            'label' => $labels,
            'data' => $data
        ];
    }


    private function buildChart(array $labels, array $data): Chart
    {
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
                    'borderWidth' => 1.3,
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
                            'enabled' => true
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
