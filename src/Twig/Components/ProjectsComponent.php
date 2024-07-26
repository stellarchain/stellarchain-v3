<?php

namespace App\Twig\Components;

use App\Entity\ProjectCategory;
use App\Repository\ProjectCategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\ProjectTypeRepository;
use App\Repository\RoundRepository;
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

    #[LiveProp(writable: true, url: true)]
    public int $type = 0;

    #[LiveProp(writable: true, url: true)]
    public string $sort = 'date';

    #[LiveProp(writable: true, url: true)]
    public int $round = 0;

    #[LiveProp(writable: true, url: true)]
    public int $award = 0;

    #[LiveProp(writable: true, url: true)]
    public int $category = 1;

    public function __construct(
        private ProjectRepository $projectRepository,
        private Security $security,
        private RoundRepository $roundRepository,
        private ProjectCategoryRepository $projectCategoryRepository,
        private ProjectTypeRepository $projectTypeRepository
    )
    {
    }

    #[LiveAction]
    public function resetPage(): void
    {
        $this->page = 1;
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        $criteria = $this->buildFilterCriteria();
        $totalProjects = $this->projectRepository->count($criteria);
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
        $criteria = $this->buildFilterCriteria();
        $orderBy = $this->getOrderBy();
        return $this->projectRepository->findBy($criteria, $orderBy, self::PER_PAGE, $offset);
    }

    /**
     * @return array<int, Round>
     */
    public function getRounds(): array
    {
        return $this->roundRepository->findBy([], []);
    }

    /**
     * @return array<int, ProjectCategory>
     */
    public function getCategories(): array
    {
        return $this->projectCategoryRepository->findBy([], []);
    }


    /**
     * @return array<int, ProjectCategory>
     */
    public function getProjectTypes(): array
    {
         return $this->projectTypeRepository->findBy(['category' => $this->category]);
    }


    /**
     * @return array|array<string,int>
     */
    private function buildFilterCriteria(): array
    {
        $criteria = [];

        if ($this->type > 0) {
            $criteria['type'] = $this->type;
        }

        if ($this->round > 0) {
            $criteria['round'] = $this->round;
        }

        if ($this->award > 0) {
            $criteria['award_type'] = $this->award;
        }

        return $criteria;
    }
    /**
     * @return array<string,string>
     */
    private function getOrderBy(): array
    {
        switch ($this->sort) {
            case 'name':
                return ['name' => 'ASC'];
            case 'date':
                return ['created_at' => 'DESC'];
            case 'budget':
                return ['budget' => 'DESC'];
            default:
                return ['created_at' => 'DESC'];
        }
    }

}
