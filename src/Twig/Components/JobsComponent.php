<?php

namespace App\Twig\Components;

use App\Repository\JobRepository;
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
    public string $type = 'worldwide';

    #[LiveProp]
    public int $page = 1;

    private const PER_PAGE = 10;

    private $jobRepository;

    private $security;

    public function __construct(JobRepository $jobRepository, Security $security)
    {
        $this->jobRepository = $jobRepository;
        $this->security = $security;
    }

    #[LiveAction]
    public function changeType(string $type): void
    {
        $this->type = $type;
        $this->page = 1;
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        //$criteria = $this->buildCriteria();
        $totalJobs = $this->jobRepository->count([]);
        return $totalJobs > ($this->page * self::PER_PAGE);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    /**
     * @return array<int, Community>
     */
    public function getJobs(): array
    {
        $offset = ($this->page - 1) * self::PER_PAGE;
        $communities = $this->jobRepository->findBy([], [], self::PER_PAGE, $offset);
        return $communities;
    }

    /**
     * @return array|array<string,string>
     */
    private function buildCriteria(): array
    {
        switch ($this->type) {
            case 'hot':
                return [];
            case 'new':
                return ['created_at' => 'DESC'];
            case 'top':
                return ['rank' => 'DESC'];
            default:
                return [];
        }
    }
}
