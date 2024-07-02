<?php

namespace App\Controller;

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
    ): Response
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45, 10, 20, 50, 40, 60, 4, 15, 100, 22, 56, 89, 33, 43, 10],
                    'fill' => false,
                    'tension' => 0.1,
                    'borderWidth' => 1,
                    'pointBorderWidth' => 0.1,
                    'pointRadius' => 0,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'display' => false,
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
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
                'position' => 'average'
            ]
        ]);
        return $this->render('market/index.html.twig', [
            'controller_name' => 'MarketController',
            'chart' => $chart
        ]);
    }
}
