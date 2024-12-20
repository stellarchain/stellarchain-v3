<?php

namespace App\Controller;

use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'app_statistics')]
    public function index(StatisticsService $statisticsService): Response
    {
        return $this->render('statistics/index.html.twig', [
            'statistics_charts' => $statisticsService->buildStatisticsCharts()
        ]);
    }

    #[Route('/statistics/{stat}', name: 'app_statistics_show', methods: ['GET'])]
    public function charts(TranslatorInterface $translator, string $stat): Response
    {
        return $this->render('statistics/show.html.twig', [

            'stat' => $stat
        ]);
    }

    #[Route('/statistics/{stat}', name: 'app_statistics_get', methods: ['POST'])]
    public function charts_data(StatisticsService $statisticsService, string $stat, Request $request): Response
    {
        $requestData = $request->toArray();
        if (!$requestData['startTime']) {
            return $this->json(['error' => 'Invalid start time provided'], Response::HTTP_BAD_REQUEST);
        }
        if (!$requestData['timeFrame']) {
            return $this->json(['error' => 'Invalid time frame provided'], Response::HTTP_BAD_REQUEST);
        }
        $chartData = $statisticsService->getMetricsData($stat, $requestData['timeFrame'], $requestData['startTime'], 200);
        $areaSeries = [];
        foreach ($chartData['labels'] as $k => $time) {
            $areaSeries[] = [
                'value' => $chartData['data'][$k],
                'time' => $time->getTimestamp()
            ];
        }
        return $this->json($areaSeries);
    }
}
