<?php

namespace App\Twig\Components;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('interesting-projects')]
final class InterestingProjects
{
    use DefaultActionTrait;

    private $projectRepository;
    private $userRepository;
    private $entityManager;
    private $security;

    public function __construct(ProjectRepository $projectRepository, EntityManagerInterface $entityManager, Security $security, UserRepository $userRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @return array<int, array>
     */
    public function getProjects(): array
    {
        $currentUser = $this->security->getUser();
        $projects = $this->projectRepository->findBy([], ['created_at' => 'DESC'], 5);

        $projectsWithFollowStatus = [];

        foreach ($projects as $project) {
            $isFollowed = $currentUser && $this->userRepository->isFollowingProject($currentUser, $project);
            $projectsWithFollowStatus[] = [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'user' => [
                    'id' => $project->getUser()->getId(),
                    'username' => $project->getUser()->getUsername(),
                ],
                'isFollowed' => $isFollowed,
            ];
        }

        return $projectsWithFollowStatus;
    }
}
