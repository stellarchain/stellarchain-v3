<?php

namespace App\Twig\Components;

use App\Entity\Location;
use App\Repository\JobCategoryRepository;
use App\Repository\JobRepository;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;


#[AsLiveComponent('jobs-component')]
final class JobsComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $location = 'worldwide';

    #[LiveProp(writable: true, url: true)]
    public int $salary = 0;

    #[LiveProp(writable: true, url: true)]
    public string $sort = 'date';

    #[LiveProp(writable: true, url: true)]
    public int $category = 0;

    #[LiveProp(writable: true)]
    public int $page = 1;

    private const PER_PAGE = 10;

    public function __construct(
        private JobRepository $jobRepository,
        private JobCategoryRepository $jobCategoryRepository,
        private LocationRepository $locationRepository,
        private Security $security
    ){}

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
        $totalJobs = $this->jobRepository->countByCriteria($criteria);
        return $totalJobs > ($this->page * self::PER_PAGE);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    /**
     * @return array<int, Job>
     */
    public function getJobs(): array
    {
        $offset = ($this->page - 1) * self::PER_PAGE;
        $criteria = $this->buildFilterCriteria();

        $orderBy = $this->buildSortCriteria();

        return $this->jobRepository->findJobs($criteria, $orderBy, $offset, self::PER_PAGE);
    }

    /**
     * @return array<int, JobCategory>
     */
    public function getJobCategories(): array
    {
        return $this->jobCategoryRepository->findAll();
    }

    /**
     * @return array<int, Location>
     */
    public function getLocations(): array
    {
        return $this->locationRepository->findAll();
    }

    /**
     * @return array<string,string>
     */
    private function buildSortCriteria(): array
    {
        switch ($this->sort) {
            case 'date':
                return ['created_at' => 'DESC'];
            case 'salary':
                return ['salary' => 'DESC'];
            case 'views':
                return [];
            case 'applied':
                return ['applied' => 'DESC'];
            case 'hot':
                return [];
            case 'benefits':
                return [];
            default:
                return [];
        }
    }

    private function buildFilterCriteria(): Criteria
    {
        $criteria = Criteria::create();

        if ($this->location !== 'worldwide') {
            $location = $this->locationRepository->find($this->location);
            if ($location) {
                $criteria->andWhere(Criteria::expr()->eq('location', $location));
            }
        }

        if ($this->salary > 0) {
            $criteria->andWhere(Criteria::expr()->gte('salary', $this->salary * 10000));
        }

        if ($this->category > 0) {
            $category = $this->jobCategoryRepository->find($this->category);
            if ($category) {
                $criteria->andWhere(Criteria::expr()->eq('category', $category));
            }
        }

        return $criteria;
    }
}
