<?php

namespace App\Twig\Components;

use App\Service\LedgerMetricsService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\CoinStatRepository;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\LiveComponent\ComponentToolsTrait;

#[AsLiveComponent('coin-chart-component')]
final class CoinChartComponent
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public string $stat;

    #[LiveProp(writable: true)]
    public int $page = 1;

    private static array $pagesData = [];

    private const PER_PAGE = 100;

    private CoinStatRepository $coinStatRepository;
    private ChartBuilderInterface $chartBuilder;
    private TranslatorInterface $translator;
    private LedgerMetricsService $ledgerMetricsService;

    public function __construct(
        CoinStatRepository $coinStatRepository,
        ChartBuilderInterface $chartBuilder,
        TranslatorInterface $translator,
        LedgerMetricsService $ledgerMetricsService,
    ) {
        $this->coinStatRepository = $coinStatRepository;
        $this->chartBuilder = $chartBuilder;
        $this->translator = $translator;
        $this->ledgerMetricsService = $ledgerMetricsService;
    }

    #[LiveAction]
    public function resetPage(): void
    {
        $this->page = 1;
    }

    public function hasMore(): bool
    {
        $totalTicks = $this->totalTicks();
        return $totalTicks > ($this->page * self::PER_PAGE);
    }

    public function totalTicks(): int
    {
        return $this->coinStatRepository->getTotalCountByName($this->stat);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    public function getChartName()
    {
        return $this->translator->trans($this->stat);
    }

    #[LiveListener('more')]
    public function getChartData()
    {
        if (!$this->hasMore()) {
            return;
        }
        ++$this->page;

        $dataSet = $this->getChartDataByKey($this->page);
        $chartData = [
            'labels' => $dataSet[0],
            'datasets' => [
                [
                    'label' => $this->translator->trans($this->stat),
                    'backgroundColor' => 'rgba(99, 106, 255, 0.82)',
                    'borderColor' => 'rgba(220, 53, 69)',
                    'pointBackgroundColor' => 'rgb(255, 99, 132)',
                    'data' => $dataSet[1],
                    'fill' => 'start',
                    'tension' => 0.4,
                    'borderWidth' => 1,
                    'pointBorderWidth' => 1,
                    'pointRadius' => 1,
                ],
            ],
        ];

        // Pagination info
        $hasMore = $this->hasMore();

        $this->dispatchBrowserEvent('chart_data', [
            'chart' => $chartData,
            'stat' => $this->stat,
            'hasMore' => $hasMore,
        ]);
    }

    public function getChartDataLedger($page): array
    {
        $endDate = new \DateTimeImmutable(); // Today
        $startDate = $endDate->sub(new \DateInterval('P1D'));
        $ledgerMetrics = $this->ledgerMetricsService->getMetricsForTimeIntervals($startDate, $endDate);

        $labels = [];
        $data = [];
        foreach ($ledgerMetrics as $entry) {
            $labels[] =  \DateTime::createFromFormat('Y-m-d H:i:s', $entry['time_start'])->format('m-d-Y H:i');
            $data[] = $entry[$this->stat];
        }
        return [$labels, $data];
    }

    public function getChartDataByKey($page): array
    {
        $priceStats = ['rank', 'market_cap', 'volume_24h', 'price_usd', 'circulating_supply', 'market_cap_dominance'];
        if (in_array($this->stat, $priceStats, true)) {

            $offset = ($page - 1) * self::PER_PAGE;
            $pageData = $this->coinStatRepository->getStatsByName($this->stat, $offset, self::PER_PAGE);
            $priceData = array_reverse($pageData);
            $labels = [];
            $data = [];
            foreach ($priceData as $entry) {
                $labels[] = \DateTime::createFromFormat('Y-m-d H:i:s', $entry['created_at'])->format('m-d-Y H:i');
                $data[] = (float) $entry['value'];
            }

            return [$labels, $data];
        } else {
            return $this->getChartDataLedger($page);
        }
    }

    public function getChart(): Chart
    {
        $chartData = $this->getChartDataByKey(1);
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $chartData[0],
            'datasets' => [
                [
                    'label' => $this->translator->trans($this->stat),
                    'backgroundColor' => 'rgba(99, 106, 255, 0.82)',
                    'borderColor' => 'rgba(220, 53, 69)',
                    'pointBackgroundColor' => 'rgb(255, 99, 132)',
                    'gradient' => [
                        'backgroundColor' => [
                            'axis' => 'y',
                            'colors' => [
                                0 => 'rgba(220, 53, 69, 0.1)',
                                PHP_INT_MAX => 'rgba(220, 53, 69, 0.5)',
                            ]
                        ],
                    ],
                    'data' => $chartData[1],
                    'fill' => 'start',
                    'tension' => 0.4,
                    'borderWidth' => 1,
                    'pointBorderWidth' => 1,
                    'pointRadius' => 1,
                ],
            ],
        ]);

        $totalDataPoints = count($chartData[1]);
        $initialViewPercentage = 0.5;
        $maxValue = $totalDataPoints - 1;
        $minValue = $maxValue - floor($totalDataPoints * $initialViewPercentage);
        $minValue = max($minValue, 0);

        $chart->setOptions([
            'class' => 'stats',
            'responsive' => true,
            'maintainAspectRatio' => true,
            'scales' => [
                'y' => [
                    'display' => true,
                    'position' => 'right',
                    'border' => [
                        'color' => '#721111'
                    ],
                    'grid' => [
                        'color' => '#2b2b2b'
                    ],
                    'type' => 'logarithmic',
                ],
                'x' => [
                    'display' => true,
                    'grid' => [
                        'color' => 'transparent'
                    ],
                ]
            ],
            'plugins' => [
                'tooltip' => [
                    'mode' => 'interpolate',
                    'position' => 'average',
                    'intersect' => false,
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
            ]
        ]);

        return $chart;
    }

    #[LiveAction]
    public function refreshChart(): void
    {
        // Method to refresh chart data
    }
}
