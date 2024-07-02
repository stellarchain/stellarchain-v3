<?php

namespace App\Service;

use App\Repository\PostRepository;
use App\Repository\ProjectRepository;

class CountService
{
    private $projectRepository;
    private $postRepository;

    public function __construct(ProjectRepository $projectRepository, PostRepository $postRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->postRepository = $postRepository;
    }

    public function getProjects(): int {
        return $this->projectRepository->count([]);
    }

    public function getPosts(): int {
        return $this->postRepository->count([]);
    }
}
