<?php

namespace App\Controller;

use App\Repository\CommunityRepository;
use App\Repository\EventRepository;
use App\Repository\JobRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DiscoverController extends AbstractController
{
    #[Route('/discover', name: 'app_discover')]
    public function index(
        ProjectRepository $projectRepository,
        EventRepository $eventRepository,
        CommunityRepository $communityRepository,
        JobRepository $jobRepository
    ): Response {

        $projects = $projectRepository->findBy([], [], 5);
        $events = $eventRepository->findBy([], [], 20);
        $communities = $communityRepository->findBy([], [], 20);
        $jobs = $jobRepository->findBy([], [], 20);

        return $this->render('discover/index.html.twig', [
            'projects' => $projects,
            'events' => $events,
            'communities' => $communities,
            'jobs' => $jobs,
        ]);
    }
}
