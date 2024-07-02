<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\CreateNewPostFormType;
use App\Repository\CoinStatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\UX\Turbo\TurboBundle;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        ChartBuilderInterface $chartBuilder,
        HubInterface $hub,
        CoinStatRepository $coinStatRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $requiredStats = ['rank', 'market_cap', 'volume_24h', 'price_usd', 'circulating_supply', 'market_cap_dominance'];
        $stellarCoinStats = $coinStatRepository->findLatestAndPreviousBySymbol('XLM', $requiredStats);
        $formattedStats = [];
        foreach ($stellarCoinStats as $stat) {
            $formattedStats[$stat['name']] = $stat;
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
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
            ]
        ]);


        $post = new Post();
        $form = $this->createForm(CreateNewPostFormType::class, $post);
        $emptyForm = clone $form;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser());
            $post->updateTimestamps();
            $entityManager->persist($post);
            $entityManager->flush();

            $data = $form->getData();

            $hub->publish(new Update(
                'posts',
                $this->renderBlock('dashboard/dashboard.html.twig', 'create', ['id' => $post->getId(), 'form' => $emptyForm])
            ));

             if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->renderBlock('dashboard/dashboard.html.twig', 'create', ['id' => $post->getId(), 'form' => $emptyForm]);
            }
        }


        $posts = $entityManager->getRepository(Post::class)->withProjects();
        return $this->render('dashboard/dashboard.html.twig', [
            'posts' => $posts,
            'chart' => $chart,
            'form' => $form,
            'stats' => $formattedStats
        ]);
    }

}
