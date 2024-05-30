<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
      /**
     * Fetch all posts with associated entities
     *
     * @return Post[] Returns an array of Post objects
     */
    public function withProjects(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.project', 'a')
            ->getQuery()
            ->getResult();
    }
}
