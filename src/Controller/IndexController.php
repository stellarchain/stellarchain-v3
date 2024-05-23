<?php

namespace App\Controller;

 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\HttpFoundation\Response;
 use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

 class IndexController extends AbstractController
 {
     #[Route('/', name: 'homepage')]
    public function index(Environment $twig): Response
     {
        return new Response($twig->render('index.html.twig'));
     }
 }
