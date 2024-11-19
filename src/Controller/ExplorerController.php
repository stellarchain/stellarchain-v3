<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExplorerController extends AbstractController
{
    #[Route('/explorer', name: 'app_explorer')]
    public function index(): Response
    {
        return $this->render('explorer/index.html.twig', [
            'controller_name' => 'ExplorerController',
        ]);
    }
}
