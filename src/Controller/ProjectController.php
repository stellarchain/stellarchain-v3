<?php

namespace App\Controller;

use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProjectFormType;
use App\Repository\RoundRepository;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'app_projects')]
    public function projects(EntityManagerInterface $entityManager): Response
    {
        $projects = $entityManager->getRepository(Project::class)->findBy([], ['id' => 'DESC']);
        return $this->render('project/index.html.twig', [
            'projects' => $projects
        ]);
    }

    #[Route('/projects/timeline', name: 'app_projects_timeline')]
    public function timeline(RoundRepository $roundRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $roundsData = $roundRepository->findAll();

        return $this->render('project/timeline.html.twig', [
            'rounds' => $roundsData
        ]);
    }

    #[Route('/projects/list', name: 'app_projects_list')]
    public function list(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        return $this->render('project/list.html.twig');
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

    #[Route('/projects/{id}', name: 'app_project_show')]
    public function project(Project $project): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        return $this->render('project/project.html.twig', [
            'project' => $project
        ]);
    }
}
