<?php

namespace App\Controller;

use App\Entity\Community;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FollowController extends AbstractController
{
    #[Route('/follow/community/{community}', name: 'app_follow_community')]
    public function community(Community $community, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user) {
            if ($community->getFollowers()->contains($user)) {
                $community->removeFollower($user);
            } else {
                $community->addFollower($user);
            }

            $entityManager->persist($community);
            $entityManager->flush();


            return $this->json([
                'followers' => $community->getFollowers()->count(),
                'community' => $community->getName(),
                'isFollowed' => $community->getFollowers()->contains($user) // Return the follow status
            ]);
        }

        return $this->json(['error' => 'Something is wrong.'], 400);
    }

    #[Route('/follow/user/{user}', name: 'app_follow_user')]
    public function user(User $user, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser) {
            $userToFollow = $user;
            if (!$userToFollow) {
                return $this->json(['error' => 'User not found'], 404);
            }
            if ($currentUser->getFollowedUsers()->contains($userToFollow)) {
                $currentUser->unfollowUser($userToFollow);
            } else {
                $currentUser->followUser($userToFollow);
            }

            $entityManager->persist($currentUser);
            $entityManager->flush();

                return $this->json(['status' => 'success']);
        }

        return $this->json(['error' => 'Something is wrong.'], 400);
    }

    #[Route('/follow/project/{project}', name: 'app_follow_project')]
    public function project(Project $project, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user) {
            if ($project->getFollowers()->contains($user)) {
                $project->removeFollower($user);
            } else {
                $project->addFollower($user);
            }

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->json([
                'followers' => $project->getFollowers()->count(),
                'project' => $project->getId(),
                'isFollowed' => $project->getFollowers()->contains($user) // Return if the user is now following
            ]);
        }

        return $this->json(['error' => 'Something is wrong.'], 400);
    }
}
