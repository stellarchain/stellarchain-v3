<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\EditAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }

    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $profile = new UserProfile();
        if ($this->getUser()->getUserProfile()){
            $profile = $this->getUser()->getUserProfile();
        }

        $form = $this->createForm(EditAccountType::class, $profile);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $profile->setUser($this->getUser());
            $entityManager->persist($profile);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_show', ['id' => $profile->getId()]);
        }

        return $this->render('account/show.html.twig', [
            'profile' => $profile,
            'edit' => true,
            'form' => $form
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function profile(User $user): Response
    {
        $isFollowed = false;
        if ($this->getUser()){
            $isFollowed = $this->getUser()->getFollowedUsers()->contains($user);
        }
        $user->isFollowed = $isFollowed;
        return $this->render('account/show.html.twig', [
            'profile' => $user,
            'edit' => false
        ]);
    }
}
