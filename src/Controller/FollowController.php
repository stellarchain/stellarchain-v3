<?php

namespace App\Controller;

use App\Entity\Community;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FollowController extends AbstractController
{
    #[Route('/follow/community/{community}', name: 'app_follow_community')]
    public function index(Community $community): Response
    {
       return $this->json([
            'followers' => 1,
            'community' => $community->getName()
        ]);
    }
}
