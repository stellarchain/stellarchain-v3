<?php

namespace App\Controller;

use App\Repository\StellarHorizon\AssetMetricRepository;
use App\Repository\StellarHorizon\AssetRepository;
use App\Service\GlobalValueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\UX\Chartjs\Model\Chart;
use Yosymfony\Toml\Toml;

class MarketController extends AbstractController
{
    #[Route('/markets', name: 'app_markets')]
    public function index(): Response
    {
        return $this->render('market/index.html.twig');
    }

    #[Route('/markets/{assetCode}-{assetIssuer}', name: 'app_markets_show_asset')]
    public function show(
        string $assetCode,
        string $assetIssuer,
        AssetRepository $assetRepository,
        AssetMetricRepository $assetMetricRepository,
        ChartBuilderInterface $chartBuilder,
        GlobalValueService $globalValueService,
        HttpClientInterface $client): Response
    {
        $asset = $assetRepository->findOneBy(['asset_code' => $assetCode, 'asset_issuer' => $assetIssuer]);
        if (!$asset){
                throw new NotFoundHttpException('Sorry not existing!');
        }
        $latestMetric = $asset->getAssetMetrics()->first();

        $assetData = [ 'asset' => $asset];
        if ($latestMetric) {
            $recentMetrics = $assetMetricRepository->findBy(['asset' => $asset],['created_at' => 'DESC'],50);
            $metricsForChart = array_reverse($recentMetrics);

            $usdXlmPrice = $globalValueService->getPrice();
            $chartData = array_map(fn ($metric) => $metric->getPrice() * $usdXlmPrice, $metricsForChart);
            $chartLabels = array_map(fn ($metric) => $metric->getCreatedAt()->format('d/m H:i'), $metricsForChart);

            $chart = $this->buildChart($chartLabels, $chartData, $chartBuilder, $assetCode);
            $assetData['latestMetric'] = $latestMetric;
            $assetData['chart'] =  $chart;
            $assetData['priceMarket'] =  $chartData[0];
            $balances = $asset->getBalances() ? $asset->getBalances()['authorized'] : 0;
            $assetData['marketCap'] = ($asset->getClaimableBalancesAmount() + $asset->getLiquidityPoolsAmount() + $asset->getContractsAmount() + $balances) * $chartData[0];
        }

        if ($asset->getToml()) {
            try {
                $response = $client->request('GET', $asset->getToml());
                $content = $response->getContent();
                $array = Toml::Parse($content);
                $currencies = $array['CURRENCIES'];
                $assetData['toml'] = $this->findArrayByCodeAndIssuer($currencies, $assetCode, $assetIssuer);

                if (isset($array['DOCUMENTATION']) && isset($array['DOCUMENTATION']['ORG_URL'])){
                    $assetData['toml']['url'] = $array['DOCUMENTATION']['ORG_URL'];
                }
            } catch (\Exception $e) {
            }
        }

        return $this->render('market/asset.html.twig', $assetData);
    }

    /**
     * @return <missing>|null
     * @param mixed $array
     * @param mixed $code
     * @param mixed $issuer
     */
    function findArrayByCodeAndIssuer(array $array, string $code, string $issuer): mixed
    {
        foreach ($array as $item) {
            if (
                isset($item['code']) && isset($item['issuer']) &&
                $item['code'] === $code && $item['issuer'] === $issuer
            ) {
                return $item;
            }
        }
        return null;
    }


    /**
     * Helper function to build the chart object
     * @param array<int,mixed> $labels
     * @param array<int,mixed> $data
     * @param mixed $chartBuilder
     * @param mixed $assetCode
     */
    private function buildChart(array $labels, array $data, $chartBuilder, $assetCode): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $totalDataPoints = count($data);
        $initialViewPercentage = 0.1;
        $maxValue = $totalDataPoints - 1;
        $minValue = $maxValue - floor($totalDataPoints * $initialViewPercentage);
        $minValue = max($minValue, 0);

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
                    'min' => $minValue,
                    'max' => $maxValue
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
