<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MarketController extends AbstractController
{
    #[Route('/markets', name: 'app_markets')]
    public function index(): Response
    {
        return $this->render('market/index.html.twig');
    }
}
