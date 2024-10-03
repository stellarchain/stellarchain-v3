<?php

namespace App\Controller;

use App\Repository\CoinStatRepository;
use App\Service\LedgerMetricsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'app_statistics')]
    public function index(): Response
    {
        return $this->render('statistics/index.html.twig', [
            'controller_name' => 'StatisticsController',
        ]);
    }

    #[Route('/statistics/ledger/{stat}', name: 'app_statistics_ledger_show')]
    public function show_ledgers(
        ChartBuilderInterface $chartBuilder,
        TranslatorInterface $translator,
        LedgerMetricsService $ledgerMetricsService,
        string $stat
    ): Response {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $endDate = new \DateTimeImmutable(); // Today
        $startDate = $endDate->sub(new \DateInterval('P1D'));

        $ledgerMetrics = $ledgerMetricsService->getMetricsForTimeIntervals($startDate, $endDate);

        $labels = [];
        $data = [];
        foreach ($ledgerMetrics as $entry) {
            $labels[] = $entry['time_start']; // Labels are the dates
            $data[] = $entry[$stat];
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $translator->trans($stat),
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'gradient' => [
                        'backgroundColor' => [
                            'axis' => 'y',
                            'colors' => [
                                0 => 'transparent',
                                PHP_INT_MAX => 'rgba(220, 53, 69, 0.5)',
                            ]
                        ],
                    ],
                    'data' => $data,
                    'fill' => -1,
                    'tension' => 0.1,
                    'borderWidth' => 1,
                    'pointBorderWidth' => 0.,
                    'pointRadius' => 2,
                ],
            ],
        ]);

        $totalDataPoints = count($data);
        $initialViewPercentage = 0.01;
        $maxValue = $totalDataPoints - 1;
        $minValue = $maxValue - floor($totalDataPoints * $initialViewPercentage);
        $minValue = max($minValue, 0);

        $chart->setOptions([
            'class' => 'stats',
            'responsive' => true,
            'scales' => [
                'y' => [
                    'display' => true,
                ],
                'x' => [
                    'display' => true,
                    'min' => $minValue,  // Dynamically set the min value
                    'max' => $maxValue,  // Dynamically set the max value
                    'position' => 'right',
                ]
            ],
            'plugins' => [
                'tooltip' => [
                    'mode' => 'interpolate',
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

        return $this->render('statistics/show.html.twig', [
            'chart' => $chart,
            'chart_name' => $translator->trans($stat)
        ]);
    }

    #[Route('/statistics/price/{stat}', name: 'app_statistics_show')]
    public function show(
        TranslatorInterface $translator,
        string $stat
    ): Response {
          return $this->render('statistics/show.html.twig', [
            'chart_name' => $translator->trans($stat),
            'stat' => $stat
        ]);
    }
}
