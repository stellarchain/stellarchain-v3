<?php

namespace App\Controller;

use App\Entity\Community;
use App\Form\CommunityFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommunityController extends AbstractController
{
    #[Route('/communities', name: 'app_communities')]
    public function index(): Response
    {
        return $this->render('community/index.html.twig');
    }

    #[Route('/communities/new', name: 'app_new_community')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $community = new Community();
        $form = $this->createForm(CommunityFormType::class, $community);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $community->setUser($this->getUser());
            $entityManager->persist($community);
            $entityManager->flush();

            return $this->redirectToRoute('app_show_communities', ['id' => $community->getId()]);
        }

        return $this->render('community/new.html.twig', [
            'form' => $form
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
