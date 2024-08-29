<?php

namespace App\Controller;

use App\Repository\StellarHorizon\AssetMetricRepository;
use App\Repository\StellarHorizon\AssetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class MarketController extends AbstractController
{
    #[Route('/markets', name: 'app_markets')]
    public function index(
        ChartBuilderInterface $chartBuilder,
        AssetRepository $assetRepository,
        AssetMetricRepository $assetMetricRepository,
    ): Response {

        $assets = $assetRepository->findBy(['in_market' => true]);
        $latestMetrics = [];
        $charts = [];

        foreach ($assets as $asset) {
            $latestMetric = $assetMetricRepository->findOneBy(
                ['asset' => $asset],
                ['created_at' => 'DESC']
            );

            if ($latestMetric) {
                $latestMetrics[$asset->getId()] = $latestMetric;

                // Fetch the last 31 metrics (including the latest one) for the chart
                $recentMetrics = $assetMetricRepository->findBy(
                    ['asset' => $asset],
                    ['created_at' => 'DESC'],
                   10
                );

                // Reverse the order to have the oldest first (for chronological plotting)
                $metricsForChart = array_reverse($recentMetrics);

                // Prepare the chart data for this asset
                $chartData = array_map(function ($metric) {
                    return $metric->getPriceChange1h();
                }, $metricsForChart);

                $chartLabels = array_map(function ($metric) {
                    return $metric->getCreatedAt()->format('H:i');
                }, $metricsForChart);

                // Create a chart for this asset
                $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
                $chart->setData([
                    'labels' => $chartLabels,
                    'datasets' => [
                        [
                            'label' => $asset->getAssetCode() . ' 1h%',
                            'backgroundColor' => 'rgb(255, 99, 132)',
                            'borderColor' => 'rgb(255, 99, 132)',
                            'data' => $chartData,
                            'fill' => false,
                            'tension' => 0.1,
                            'borderWidth' => 1,
                            'pointBorderWidth' => 0.1,
                            'pointRadius' => 2,
                        ],
                    ],
                ]);

                $chart->setOptions([
                    'layout' => [
                        'padding' => [
                            'bottom' => 25
                        ]
                    ],
                    'scales' => [
                        'y' => [
                            'display' => false,
                        ],
                        'x' => [
                            'display' => false,
                        ]
                    ],
                    'plugins' => [
                        'legend' => [
                            'display' => false,
                        ]
                    ],
                    'tooltips' => [
                        'enabled' => true,
                        'yAlign' => 'center',
                        'position' => 'nearest',
                    ],
                ]);

                // Store the chart in the charts array
                $charts[$asset->getId()] = $chart;
            }
        }

        // Sort the assets based on the latest price in descending order
        uasort($latestMetrics, function ($a, $b) {
            return $b->getPrice() <=> $a->getPrice(); // Descending order
        });

        // Update the assets array to match the sorted metrics
        $assets = array_map(function ($metric) {
            return $metric->getAsset();
        }, $latestMetrics);

        return $this->render('market/index.html.twig', [
            'assets' => $assets,
            'latestMetrics' => $latestMetrics,
            'charts' => $charts,
        ]);
    }
}
