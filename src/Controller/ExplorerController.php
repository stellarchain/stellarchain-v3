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

    #[Route('/transactions/{hash}', name: 'app_explorer_transaction')]
    public function transaction(string $hash): Response
    {
        return $this->render('explorer/transaction/index.html.twig', [
            'hash' => $hash,
        ]);
    }

    #[Route('/accounts/{hash}', name: 'app_explorer_account')]
    public function account(string $hash): Response
    {
        return $this->render('explorer/accounts/index.html.twig', [
            'hash' => $hash,
        ]);
    }
}
