<?php
namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('users-to-follow')]
final class UsersToFollow
{
    use DefaultActionTrait;

    private $userRepository;
    private $entityManager;
    private $security;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @return array<int, array>
     */
    public function getUsers(): array
    {
        $currentUser = $this->security->getUser();
        $users = $this->userRepository->findBy(['isVerified' => true]);

        $usersWithFollowStatus = [];

        foreach ($users as $user) {
            $isFollowed = $currentUser && $currentUser->getFollowedUsers()->contains($user);
            $usersWithFollowStatus[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'username' => $user->getUsername(),
                'isFollowed' => $isFollowed,
            ];
        }

        return $usersWithFollowStatus;
    }

    public function toggleFollowUser(int $userId): void
    {
        $currentUser = $this->security->getUser();

        /** @var User $userToFollow */
        $userToFollow = $this->userRepository->find($userId);

        if ($userToFollow) {
            if ($currentUser->getFollowedUsers()->contains($userToFollow)) {
                // Unfollow logic
                $currentUser->unfollowUser($userToFollow);
            } else {
                // Follow logic
                $currentUser->followUser($userToFollow);
            }

            $this->entityManager->persist($currentUser);
            $this->entityManager->flush();
        }
    }
}
