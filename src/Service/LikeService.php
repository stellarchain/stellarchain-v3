<?php


namespace App\Service;

use App\Repository\LikeRepository;

class LikeService
{
    private $likeRepository;

    public function __construct(LikeRepository $likeRepository)
    {
        $this->likeRepository = $likeRepository;
    }

    public function countLikesForProject(int $projectId): int
    {
        return $this->likeRepository->countLikes('project', $projectId);
    }


    public function countLikesForPost(int $postId): int
    {
        return $this->likeRepository->countLikes('post', $postId);
    }
}
