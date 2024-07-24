<?php

namespace App\Twig\Components;

use App\Repository\ProjectRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;


#[AsLiveComponent('projects-component')]
final class ProjectsComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;


    #[LiveProp]
    public int $page = 1;

    private const PER_PAGE = 20;

    private $projectRepository;

    private $security;

    public function __construct(ProjectRepository $projectRepository, Security $security)
    {
        $this->projectRepository = $projectRepository;
        $this->security = $security;
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        $totalProjects = $this->projectRepository->count([]);
        return $totalProjects > ($this->page * self::PER_PAGE);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    /**
     * @return array<int, Project>
     */
    public function getProjects(): array
    {
        $offset = ($this->page - 1) * self::PER_PAGE;
        $communities = $this->projectRepository->findBy([], [], self::PER_PAGE, $offset);
        return $communities;
    }

}
