<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExplorerController extends AbstractController
{
    #[Route('/explorer/ledgers', name: 'app_explorer_ledgers')]
    public function index(): Response
    {
        return $this->render('explorer/ledger/index.html.twig', [
            'controller_name' => 'ExplorerController',
        ]);
    }
}
