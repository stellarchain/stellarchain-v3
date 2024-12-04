<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/explorer/transaction', name: 'app_explorer_transaction')]
    public function transaction(Request $request): Response
    {
        return $this->render('explorer/transaction/index.html.twig', [
            'hash' => $request->query->get('hash'),
        ]);
    }
}
