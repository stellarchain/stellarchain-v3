<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
            ->orderBy('p.created_at', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param mixed $postsPerPage
     * @param mixed $page
     *
     * @return Paginator
     */

    public function getPaginatedPosts($page = 1, $postsPerPage = 9): Paginator
    {

        $query = $this->createQueryBuilder('a')

            ->orderBy('a.created_at', 'DESC')

            ->getQuery();

        $paginator = new Paginator($query);

        $paginator->getQuery()

            ->setFirstResult($postsPerPage * ($page - 1))

            ->setMaxResults($postsPerPage);

        return $paginator;
    }
}
