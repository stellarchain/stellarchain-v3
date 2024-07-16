<?php

namespace App\Service;

use App\Repository\CommunityRepository;
use App\Repository\EventRepository;
use App\Repository\JobRepository;
use App\Repository\PostRepository;
use App\Repository\ProjectRepository;

class CountService
{
    private $projectRepository;
    private $postRepository;
    private $communityRepository;
    private $jobRepository;
    private $eventRepository;

    public function __construct(ProjectRepository $projectRepository, PostRepository $postRepository, JobRepository $jobRepository, CommunityRepository $communityRepository, EventRepository $eventRepository)
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
}
