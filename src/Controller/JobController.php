<?php

namespace App\Controller;

use App\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function new(): Response
    {
        return $this->render('job/new.html.twig', []);
    }

    #[Route('/jobs/{id}', name: 'app_show_jobs')]
    public function show(Job $job): Response
    {
        return $this->render('job/show.html.twig', [
            'job' => $job
        ]);
    }

}
