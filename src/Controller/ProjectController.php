<?php

namespace App\Controller;

use App\Entity\Project;
use App\Service\LikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProjectFormType;

class ProjectController extends AbstractController
{
    private $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    #[Route('/projects', name: 'app_projects')]
    public function projects(EntityManagerInterface $entityManager): Response
    {
        $projects = $entityManager->getRepository(Project::class)->findBy([], ['id' => 'DESC']);
        $projectsWithLikes = [];

        foreach ($projects as $project) {
            $projectsWithLikes[] = [
                'project' => $project,
                'likes' => $this->likeService->countLikesForProject($project->getId()),
            ];
        }

        return $this->render('project/index.html.twig', [
            'projects' => $projectsWithLikes
        ]);
    }

    #[Route('/popular', name: 'app_popular')]
    public function popular(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $projects = $entityManager->getRepository(Project::class)->findProjectsWithLikes();

        return $this->render('project/index.html.twig', [
            'projects' => $projects
        ]);
    }

    #[Route('/latest', name: 'app_latest')]
    public function latest(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $projects = $entityManager->getRepository(Project::class)->findProjectsWithLikes();

        return $this->render('project/index.html.twig', [
            'projects' => $projects
        ]);
    }

    #[Route('/featured', name: 'app_featured')]
    public function featured(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $projects = $entityManager->getRepository(Project::class)->findProjectsWithLikes();

        return $this->render('project/index.html.twig', [
            'projects' => $projects
        ]);
    }

    #[Route('/projects/new', name: 'app_project_add')]
    public function add_project(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $project = new Project();
        $form = $this->createForm(ProjectFormType::class, $project, [
            'action' => $this->generateUrl('app_project_add'),
        ]);
        $form->handleRequest($request);

        $response = new Response(null, 200);
        if ($form->isSubmitted() && $form->isValid()) {
            $project->updateTimestamps();
            $project->setUser($this->getUser());
            $entityManager->persist($project);
            $entityManager->flush();
            $response = new Response(null, 422);
        }

        return $this->render('project/project_add.html.twig', [
            'projectForm' => $form,
        ], $response);
    }

    #[Route('/projects/{slug}', name: 'app_project_show')]
    public function project(EntityManagerInterface $entityManager, string $slug): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $project = $entityManager->getRepository(Project::class)->findOneBy(
            ['slug' => $slug], // Filter by slug
            ['created_at' => 'DESC'] // Sort by creation date descending
        );

        return $this->render('project/project.html.twig', [
            'project' => $project
        ]);
    }
}
