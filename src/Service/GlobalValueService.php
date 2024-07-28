<?php

namespace App\Service;

use App\Repository\CoinStatRepository;
use App\Repository\CommunityRepository;
use App\Repository\EventRepository;
use App\Repository\JobRepository;
use App\Repository\PostRepository;
use App\Repository\ProjectRepository;

class GlobalValueService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private PostRepository $postRepository,
        private JobRepository $jobRepository,
        private CommunityRepository $communityRepository,
        private EventRepository $eventRepository,
        private CoinStatRepository $coinStatRepository
    )
    {
        $this->projectRepository = $projectRepository;
        $this->postRepository = $postRepository;
        $this->communityRepository = $communityRepository;
        $this->jobRepository = $jobRepository;
        $this->eventRepository = $eventRepository;
    }

    public function getProjects(): int {
        return $this->projectRepository->count([]);
    }

    public function getPosts(): int {
        return $this->postRepository->count([]);
    }

    public function getJobs(): int {
        return $this->jobRepository->count([]);
    }

    public function getCommunities(): int {
        return $this->communityRepository->count([]);
    }

    public function getEvents(): int {
        return $this->eventRepository->count([]);
    }

    /**
     * @return mixed|bool
     */
    public function getPrice(): array {
        $requiredStats = ['price_usd'];
        $stellarCoinStats = $this->coinStatRepository->findLatestAndPreviousBySymbol('XLM', $requiredStats);
        return end($stellarCoinStats);
    }

}
