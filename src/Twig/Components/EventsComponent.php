<?php

namespace App\Twig\Components;

use App\Entity\Community;
use App\Repository\CommunityRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;


#[AsLiveComponent('events-component')]
final class EventsComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $type = 'hot';

    #[LiveProp]
    public int $page = 1;

    private const PER_PAGE = 10;

    private $eventRepository;

    private $security;

    public function __construct(EventRepository $eventRepository, Security $security)
    {
        $this->eventRepository = $eventRepository;
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
        $totalEvents = $this->eventRepository->count([]);
        return $totalEvents > ($this->page * self::PER_PAGE);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    /**
     * @return array<int, Community>
     */
    public function getEvents(): array
    {
        $offset = ($this->page - 1) * self::PER_PAGE;
        $events = $this->eventRepository->findBy([], [], self::PER_PAGE, $offset);
        return $events;
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
