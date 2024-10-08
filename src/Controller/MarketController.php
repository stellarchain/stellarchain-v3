<?php

namespace App\Controller;

use App\Repository\StellarHorizon\AssetMetricRepository;
use App\Repository\StellarHorizon\AssetRepository;
use App\Service\GlobalValueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class MarketController extends AbstractController
{
    #[Route('/markets', name: 'app_markets')]
    public function index(): Response
    {
        return $this->render('market/index.html.twig');
    }

    #[Route('/markets/{assetCode}-{assetIssuer}', name: 'app_markets_show_asset')]
    public function show(string $assetCode, $assetIssuer, AssetRepository $assetRepository, AssetMetricRepository $assetMetricRepository, ChartBuilderInterface $chartBuilder, GlobalValueService $globalValueService): Response
    {
        $asset = $assetRepository->findOneBy(['asset_code' => $assetCode, 'asset_issuer' => $assetIssuer]);

        $latestMetric = $asset->getAssetMetrics()->first();

        $assetData = [
            'asset' => $asset,
        ];

        if ($latestMetric) {
            $recentMetrics = $assetMetricRepository->findBy(
                ['asset' => $asset],
                ['created_at' => 'DESC'],
                50
            );
            $metricsForChart = array_reverse($recentMetrics);

            $usdXlmPrice = $globalValueService->getPrice();
            $chartData = array_map(fn ($metric) => $metric->getPrice() * $usdXlmPrice, $metricsForChart);
            $chartLabels = array_map(fn ($metric) => $metric->getCreatedAt()->format('d/m H:i'), $metricsForChart);

            $chart = $this->buildChart($chartLabels, $chartData, $chartBuilder, $assetCode);
            $assetData['latestMetric'] = $latestMetric;
            $assetData['chart'] =  $chart;
            $assetData['priceMarket'] =  $chartData[0];
            $balances = $asset->getBalances() ? $asset->getBalances()['authorized'] : 0;
            $assetData['marketCap'] = ($asset->getClaimableBalancesAmount() + $asset->getLiquidityPoolsAmount() + $asset->getContractsAmount() + $balances) * $chartData[0] * $usdXlmPrice;
            //latestMetric.price * globalValues.price
        }

        return $this->render('market/asset.html.twig', $assetData);
    }


    /**
     * Helper function to build the chart object
     */
    private function buildChart(array $labels, array $data, $chartBuilder, $assetCode): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $assetCode,
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
                    'data' => $data,
                    'fill' => 'start',
                    'tension' => 0.4,
                    'borderWidth' => 1,
                    'pointBorderWidth' => 1,
                    'pointRadius' => 1,
                ],
            ],
        ]);
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
}
