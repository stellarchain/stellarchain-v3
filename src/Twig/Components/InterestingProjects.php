<?php

namespace App\Twig\Components;

use App\Repository\ProjectRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('interesting-projects')]
final class InterestingProjects
{
    use DefaultActionTrait;

    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * @return array<int,Project>
     */
    public function getProjects(): array
    {
        return $this->projectRepository->findBy([], ['created_at' => 'DESC'], 5);
    }
}
