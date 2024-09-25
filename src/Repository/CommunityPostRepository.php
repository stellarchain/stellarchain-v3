<?php

namespace App\Repository;

use App\Entity\Community;
use App\Entity\CommunityPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommunityPost>
 */
class CommunityPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommunityPost::class);
    }

    /**
     * Retrieve community posts ordered by creation date
     */
    public function getCommunityPosts(Community $community)
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.community = :community')
            ->setParameter('community', $community)
            ->orderBy('cp.created_at', 'DESC') // or 'ASC' for ascending order
            ->getQuery()
            ->getResult();
    }

    public function countTotalPosts(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTotalLikes(): int
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.likesCount)') // Assuming you have a likesCount field in your post
            ->getQuery()
            ->getSingleScalarResult();
    }
}
