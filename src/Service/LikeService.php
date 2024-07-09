<?php

namespace App\Service;

use App\Entity\Like;
use App\Entity\User;
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

    public function countLikesForEntity(int $postId, string $entityType): int
    {
        return $this->likeRepository->countLikes($entityType, $postId);
    }

    public function isLikedByUser(int $postId, string $entityType, User $user): bool
    {
        $like = $this->entityManager
            ->getRepository(Like::class)
            ->findOneBy([
                'entity_id' => $postId,
                'entity_type' => $entityType,
                'user' => $user
            ]);

        return $like !== null;
    }

    public function like(string $entityType, int $entityId, User $user): void
    {
        $existingLike = $this->likeRepository->findOneBy([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user' => $user,
        ]);

        if (!$existingLike) {
            $like = new Like();
            $like->setEntityType($entityType);
            $like->setEntityId($entityId);
            $like->setUser($user);
            $like->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($like);
            $this->entityManager->flush();
        }
    }

    public function unlike(string $entityType, int $entityId, User $user): void
    {
        $like = $this->likeRepository->findOneBy([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user' => $user,
        ]);

        if ($like) {
            $this->entityManager->remove($like);
            $this->entityManager->flush();
        }
    }
}
