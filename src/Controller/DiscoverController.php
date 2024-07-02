<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DiscoverController extends AbstractController
{
    #[Route('/discover', name: 'app_discover')]
    public function index(): Response
    {
        return $this->render('discover/index.html.twig', [
            'controller_name' => 'DiscoverController',
        ]);
    }
}
