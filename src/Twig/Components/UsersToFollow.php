<?php

namespace App\Twig\Components;

use App\Repository\UserRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('users-to-follow')]
final class UsersToFollow
{
    use DefaultActionTrait;

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return array<int,Project>
     */
    public function getUsers(): array
    {
        return $this->userRepository->findBy([]);
    }
}
