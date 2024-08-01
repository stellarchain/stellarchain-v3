<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CommunityRepository;
use App\Repository\EventRepository;
use App\Repository\JobRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->withProjects();
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(
        ProjectRepository $projectRepository,
        EventRepository $eventRepository,
        CommunityRepository $communityRepository,
        JobRepository $jobRepository,
        Request $request
    ): Response {
        $query = $request->query->get('q');
        $projects = $projectRepository->findByNameLike($query);
        $events = $eventRepository->findByNameLike($query);
        $communities = $communityRepository->findByNameLike($query);
        $jobs = $jobRepository->findByNameLike($query);

        return $this->render('search.html.twig', [
            'search' => $query,
            'projects' => $projects,
            'events' => $events,
            'communities' => $communities,
            'jobs' => $jobs,
        ]);
    }
}
