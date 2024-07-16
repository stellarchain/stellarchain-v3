<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class JobController extends AbstractController
{
    #[Route('/jobs', name: 'app_jobs')]
    public function index(): Response
    {
        return $this->render('job/index.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }

    #[Route('/jobs/new', name: 'app_new_job')]
    public function new(): Response
    {
        return $this->render('job/new.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }
}
