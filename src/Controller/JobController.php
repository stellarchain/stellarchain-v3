<?php

namespace App\Controller;

use App\Entity\Job;
use App\Form\NewJobFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class JobController extends AbstractController
{
    #[Route('/jobs', name: 'app_jobs')]
    public function index(): Response
    {
        return $this->render('job/index.html.twig', []);
    }

    #[Route('/jobs/new', name: 'app_new_job')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $job = new Job();
        $form = $this->createForm(NewJobFormType::class, $job);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $job->setUser($this->getUser());
            $entityManager->persist($job);
            $entityManager->flush();

            return $this->redirectToRoute('app_show_jobs', ['id' => $job->getId()]);
        }

        return $this->render('job/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/jobs/{id}', name: 'app_show_jobs')]
    public function show(Job $job): Response
    {
        return $this->render('job/show.html.twig', [
            'job' => $job
        ]);
    }
}
