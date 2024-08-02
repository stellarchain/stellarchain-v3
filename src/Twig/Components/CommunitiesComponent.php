<?php

namespace App\Twig\Components;

use App\Entity\Community;
use App\Repository\CommunityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;


#[AsLiveComponent('communities-component')]
final class CommunitiesComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public int $page = 1;

    #[LiveProp(writable: true, url: true)]
    public string $order = 'followers';

    private const PER_PAGE = 10;

    private $communityRepository;

    private $security;

    public function __construct(CommunityRepository $communityRepository, Security $security)
    {
        $this->communityRepository = $communityRepository;
        $this->security = $security;
    }

    #[LiveAction]
    public function changeOrder(string $order): void
    {
        $this->order = $order;
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
        $totalCommunities = $this->communityRepository->count([]);
        return $totalCommunities > ($this->page * self::PER_PAGE);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    /**
     * @return array<int, Community>
     */
    public function getCommunities(): array
    {

        $criteria = $this->buildCriteria();
        $offset = ($this->page - 1) * self::PER_PAGE;
        $communities = $this->communityRepository->findBy([], $criteria, self::PER_PAGE, $offset);
        return $communities;
    }

    /**
     * @return array|array<string,string>
     */
    private function buildCriteria(): array
    {
        switch ($this->order) {
            case 'followers':
                return [];
            case 'posts':
                return [];
            case 'oldest':
                return ['created_at' => 'DESC'];
            case 'newest':
                return ['created_at' => 'ASC'];
            default:
                return [];
        }
    }
}
