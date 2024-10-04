<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        TranslatorInterface $translator,
        string $stat
    ): Response {
        return $this->render('statistics/show.html.twig', [
            'chart_name' => $translator->trans($stat),
            'stat' => $stat
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
