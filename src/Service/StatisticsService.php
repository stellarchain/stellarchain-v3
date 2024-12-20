<?php

namespace App\Service;

use App\Config\Metric;
use App\Entity\AggregatedMetrics;
use App\Repository\AggregatedMetricsRepository;
use App\Repository\MetricRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatisticsService
{
    public function __construct(
        private MetricRepository $metricsRepository,
        private AggregatedMetricsRepository $aggregatedMetricsRepository,
        private ChartBuilderInterface $chartBuilder,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function chartsGrid(): array
    {
        return [
            'market-charts' => [
                Metric::PriceUsd->label() => true,
                Metric::Rank->label() => true,
                Metric::MarketCap->label() => true,
                Metric::Volume24h->label() => true,
                Metric::CirculatingSupply->label() => true,
                Metric::MarketCapDominance->label() => true,
                Metric::Trades->label() => true,
                Metric::DexVol->label() => true,
                Metric::XmlTotalPay->label() => true,
                Metric::Trades->label() => true,
                'trades-volume' => false,
            ],
            'blockchain-charts' => [
                Metric::Ledgers->label() => true,
                Metric::Tps->label() => true,
                Metric::Ops->label() => true,
                Metric::TxLedger->label() => true,
                Metric::TxSuccess->label() => true,
                Metric::TxFailed->label() => true,
                Metric::OpsLedger->label() => true,
                Metric::Transactions->label() => true,
                Metric::Operations->label() => true,
                Metric::AvgLedgerSec->label() => true,
            ],
            'network-charts' => [
                Metric::Accounts->label() => true,
                Metric::Assets->label() => true,
                Metric::OutputValue->label() => true,
                Metric::TopAccounts->label() => true,
                Metric::Invocations->label() => true,
                Metric::Contracts->label() => true,
                Metric::FeeCharged->label() => true,
                Metric::MaxFee->label() => true,
                Metric::ActiveAddresses->label() => true,
                Metric::InactiveAddresses->label() => true,
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
    public function getMetricsData(string $key, string $timeframe, int $startTime, int $limit = 50): array
    {
        $metrics = $this->aggregatedMetricsRepository->findMetricsAfterTimestamp($key, $timeframe, $startTime, $limit);
        $labels = array_map(fn ($metric) => $metric->getCreatedAt(), $metrics);

        $avgKeys = [
            'avg-ledger-sec',
            'market-cap',
            'circulating-supply',
            'market-cap-dominance',
            'tps',
            'ops',
            'top-accounts',
            'tx-ledger',
            'tx-success',
            'tx-failed',
            'ops-ledger',
            'active-addresses',
            'inactive-addresses',
            'assets',
            'accounts',
        ];

        $maxKeys = [
            'price-usd',
            'rank'
        ];
        $data = array_map(function ($metric) use ($key, $avgKeys, $maxKeys) {
            if (in_array($key, $avgKeys)) {
                $val = round((float) $metric->getAvgValue(), 4);
            } elseif (in_array($key, $maxKeys)) {
                $val = round((float) $metric->getMaxValue(), 4);
            } else {
                $val = round((float) $metric->getTotalValue(), 4);
            }
            return $val;
        }, $metrics);

        return [
            'labels' => array_reverse($labels),
            'data' => array_reverse($data)
        ];
    }

    public function buildStatisticsCharts(): array
    {
        $statistics = $this->chartsGrid();
        foreach ($statistics as $typeKey => $statisticKey) {
            foreach ($statisticKey as $key => $chart) {
                if ($chart) {
                    $metrics = $this->getMetricsData($key, '1d', time(), 60);
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

    public function aggregateMetric($metrics, $timeframe, $metricEnum, \DateTime $batchStartDate)
    {
        if (empty($metrics)) {
            return;
        }

        $totalEntries = 0;
        $totalValue = 0;
        $minValue = PHP_INT_MAX;
        $maxValue = -PHP_INT_MAX;
        $sumValue = 0;

        foreach ($metrics as $metric) {
            $totalEntries++;
            $metricValue = (float) $metric->getTotalValue();

            $totalValue += $metricValue;
            $sumValue += $metricValue;

            if ($metricValue < $minValue) {
                $minValue = $metricValue;
            }

            if ($metricValue > $maxValue) {
                $maxValue = $metricValue;
            }
        }

        $avgValue = $totalEntries > 0 ? $sumValue / $totalEntries : 0;

        $batchStartDateImmutable = \DateTimeImmutable::createFromMutable($batchStartDate);
        $aggregateMetric = new AggregatedMetrics();
        $aggregateMetric
            ->setTotalEntries($totalEntries)
            ->setMetricId($metricEnum)
            ->setTotalValue($totalValue)
            ->setAvgValue($avgValue)
            ->setMaxValue($maxValue)
            ->setMinValue($minValue)
            ->setCreatedAt($batchStartDateImmutable)
            ->setTimeframe($timeframe);

        $this->entityManager->persist($aggregateMetric);
        $this->entityManager->flush();
    }


    /**
     * @param array<int,mixed> $labels
     * @param array<int,mixed> $data
     */
    private function buildChart(array $labels, array $data): Chart
    {
        $labels = array_map(fn ($label) => $label->format('d M Y H:i'), $labels);
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
