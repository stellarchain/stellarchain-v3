<?php

namespace App\Twig\Components;

use App\Repository\CommunityPostRepository;
use App\Repository\CommunityRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;


#[AsLiveComponent('sidebar-communities-component')]
final class SidebarCommunityComponent
{
    use DefaultActionTrait;

    public function __construct(
        private UserRepository $userRepository,
        private CommunityPostRepository $communityPostRepository,
        private Security $security,
        private CommunityRepository $communityRepository)
    {
    }

    public function getTopUsers(): array
    {
        // Get the currently logged-in user
        $currentUser = $this->security->getUser();

        // Fetch top users by post count
        $topUsers = $this->userRepository->findTopUsersByPostCount(10);

        // Check if the current user is following each top user
        foreach ($topUsers as &$user) {
            $user[0]->isFollowed = $this->isUserFollowed($currentUser, $user[0]);
        }

        return $topUsers;
    }

    private function isUserFollowed($currentUser, $user): bool
    {
        if ($currentUser === null) {
            return false;
        }

        return $this->userRepository->isFollowing($currentUser, $user);
    }

    public function getTotalFollowers(): int
    {
        return $this->userRepository->countUniqueFollowers();
    }

    public function totalPosts(): int
    {
        return $this->communityPostRepository->countTotalPosts();
    }

    public function getTotalLikes(): int
    {
        return $this->communityPostRepository->countTotalLikes();
    }

    public function getTotalCommunities(): int
    {
        return $this->communityRepository->count();
    }
}
