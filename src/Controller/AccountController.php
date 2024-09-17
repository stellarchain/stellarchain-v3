<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('account/index.html.twig');
    }

    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('account/show.html.twig', [
            'profile' => $this->getUser(),
            'edit' => true,
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function profile(User $user): Response
    {
        return $this->render('account/show.html.twig', [
            'profile' => $user,
            'edit' => false
        ]);
    }
}
