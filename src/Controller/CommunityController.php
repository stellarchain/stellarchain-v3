<?php

namespace App\Controller;

use App\Entity\Community;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommunityController extends AbstractController
{
    #[Route('/communities', name: 'app_communities')]
    public function index(): Response
    {
        return $this->render('community/index.html.twig', [
            'controller_name' => 'CommunityController',
        ]);
    }


    #[Route('/communities/posts', name: 'app_communities_posts')]
    public function posts(): Response
    {
        return $this->render('community/index.html.twig', [
            'controller_name' => 'CommunityController',
        ]);
    }


    #[Route('/communities/oldest', name: 'app_communities_oldest')]
    public function oldest(): Response
    {
        return $this->render('community/index.html.twig', [
            'controller_name' => 'CommunityController',
        ]);
    }

    #[Route('/communities/oldest', name: 'app_communities_newest')]
    public function newest(): Response
    {
        return $this->render('community/index.html.twig', [
            'controller_name' => 'CommunityController',
        ]);
    }

    #[Route('/communities/{id}', name: 'app_show_communities')]
    public function show(Community $community): Response
    {
        return $this->render('community/show.html.twig', [
            'controller_name' => 'CommunityController',
            'community' => $community
        ]);
    }
}
