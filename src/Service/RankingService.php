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
        // Constants for activity weights
        $likeWeight = 1;
        $commentWeight = 2;
        $replyWeight = 1.5;
        $viewWeight = 0.5;

        // Get activity counts
        $likes = $this->postRepository->getLikesCount($post);
        $comments = $post->getComments()->count();
        $replies = $post->getTotalRepliesCount(); // Assuming you have a method to count replies
        $views = $post->getViews(); // Assuming you have a method to count views

        // Calculate base rank from activity
        $activityRank = ($likes * $likeWeight) + ($comments * $commentWeight) + ($replies * $replyWeight) + ($views * $viewWeight);

        // Calculate time decay factor
        $timeFactor = $this->calculateTimeFactor($post->getCreatedAt());

        // Calculate final rank
        $rank = $activityRank * $timeFactor;

        return $rank;
    }

    private function calculateTimeFactor(\DateTimeInterface $createdAt): float
    {
        $now = new \DateTime();
        $interval = $now->diff($createdAt);

        // Example: Rank decays over time (24 hours = 1 day = 1, reducing factor by half every day)
        $days = $interval->days;

        return 1 / (1 + $days); // This is a simple time decay formula
    }
}
