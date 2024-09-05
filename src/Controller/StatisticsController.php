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

    #[Route('/statistics/price/{stat}', name: 'app_statistics_show')]
    public function show(
        ChartBuilderInterface $chartBuilder,
        CoinStatRepository $coinStatRepository,
        TranslatorInterface $translator,
        LedgerMetricsService $ledgerMetricsService,
        string $stat
    ): Response {

        $standardStats = ['rank', 'market_cap', 'volume_24h', 'price_usd', 'circulating_supply', 'market_cap_dominance'];
        if (!in_array($stat, $standardStats, true)) {
            throw $this->createNotFoundException("The stat '{$stat}' was not found.");
        }

        $endDate = new \DateTimeImmutable(); // Today
        $startDate = $endDate->sub(new \DateInterval('P1D'));

        $ledgerMetrics = $ledgerMetricsService->getMetricsForTimeIntervals($startDate, $endDate);

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $priceData = $coinStatRepository->getStatsByName($stat);
        $labels = [];
        $data = [];
        foreach ($priceData as $entry) {
            $labels[] = \DateTime::createFromFormat('Y-m-d H:i:s', $entry['created_at'])->format('d M h:i'); // Labels are the dates
            $data[] = (float) $entry['value'];
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $translator->trans($stat),
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $data,
                    'fill' => false,
                    'tension' => 0.1,
                    'borderWidth' => 1,
                    'pointBorderWidth' => 0.,
                    'pointRadius' => 2,
                ],
            ],
        ]);

        $totalDataPoints = count($data);
        $initialViewPercentage = 0.05;
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
}
