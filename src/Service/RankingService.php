<?php

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;


class RankingService
{
    private PostRepository $postRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
    }

    public function updateRank(Post $post): void
    {
        $rank = $this->calculateRank($post);
        $post->setRank($rank);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    private function calculateRank(Post $post): float
    {
        $likeWeight = 1;
        $commentWeight = 2;
        $replyWeight = 1.5;
        $viewWeight = 0.5;

        $likes = $this->postRepository->getLikesCount($post);
        $comments = $post->getComments()->count();
        $replies = $post->getTotalRepliesCount();
        $views = $post->getViews();

        $activityRank = ($likes * $likeWeight) + ($comments * $commentWeight) + ($replies * $replyWeight) + ($views * $viewWeight);
        $timeFactor = $this->calculateTimeFactor($post->getCreatedAt());
        $rank = $activityRank ;

        return $rank;
    }

    private function calculateTimeFactor(\DateTimeInterface $createdAt): float
    {
        $now = new \DateTime();
        $interval = $now->diff($createdAt);
        $days = $interval->days;

        return 1 / (1 + $days);
    }
}
