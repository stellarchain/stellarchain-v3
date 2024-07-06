<?php

namespace App\Service;

use App\Entity\Like;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;

class LikeService
{
    private $likeRepository;
    private $entityManager;

    public function __construct(LikeRepository $likeRepository, EntityManagerInterface $entityManager)
    {
        $this->likeRepository = $likeRepository;
        $this->entityManager = $entityManager;
    }

    public function countLikesForProject(int $projectId): int
    {
        return $this->likeRepository->countLikes('project', $projectId);
    }


    public function countLikesForPost(int $postId): int
    {
        return $this->likeRepository->countLikes('post', $postId);
    }

    public function like(string $entityType, int $entityId, ?int $userId): void
    {
        $existingLike = $this->likeRepository->findOneBy([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
        ]);

        if (!$existingLike) {
            $like = new Like();
            $like->setEntityType($entityType);
            $like->setEntityId($entityId);
            $like->setUser($userId);
            $this->entityManager->persist($like);
            $this->entityManager->flush();
        }
    }

    public function unlike(string $entityType, int $entityId, int $userId): void
    {
        $like = $this->likeRepository->findOneBy([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
        ]);

        if ($like) {
            $this->entityManager->remove($like);
            $this->entityManager->flush();
        }
    }
}
