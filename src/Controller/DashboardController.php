<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Project;
use App\Form\SearchBarFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $posts = $entityManager->getRepository(Post::class)->withProjects();

        return $this->render('dashboard/index.html.twig', [
            'posts' => $posts
        ]);
    }

}
