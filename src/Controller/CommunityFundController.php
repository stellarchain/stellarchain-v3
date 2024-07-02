<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommunityFundController extends AbstractController
{
    #[Route('/fund', name: 'app_community_fund')]
    public function index(): Response
    {
        return $this->render('community_fund/index.html.twig', [
            'controller_name' => 'CommunityFundController',
        ]);
    }
}
