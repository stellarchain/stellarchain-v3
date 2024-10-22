<?php

namespace App\Controller;

use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'app_statistics')]
    public function index(StatisticsService $statisticsService): Response
    {
        $cache = new FilesystemAdapter();
        $statisticsCharts = $cache->get('statistics_chart', function (ItemInterface $item) use ($statisticsService): array {
            $item->expiresAfter(3600);
            return $statisticsService->buildStatisticsCharts();
        });
        return $this->render('statistics/index.html.twig', [
            'statistics_charts' => $statisticsCharts
        ]);
    }

    #[Route('/statistics/{stat}/{chart}', name: 'app_statistics_show', methods: ['GET'])]
    public function charts(TranslatorInterface $translator, string $stat, string $chart): Response
    {
        return $this->render('statistics/show.html.twig', [
            'chart_name' => $translator->trans($stat . '.' . $chart . '.title'),
            'stat' => $stat
        ]);
    }

    #[Route('/statistics/{stat}/{chart}', name: 'app_statistics_get', methods: ['POST'])]
    public function charts_data(StatisticsService $statisticsService, string $stat, string $chart): Response
    {
        $chartData = $statisticsService->getMetricsData($stat, $chart);
        $areaSeries = [];
        foreach ($chartData['label'] as $k => $time) {
            $areaSeries[] = [
                'value' => $chartData['data'][$k],
                'time' => \DateTime::createFromFormat('m-d-Y H:i', $time)->getTimestamp()
            ];
        }
        return $this->json($areaSeries);
    }
}
