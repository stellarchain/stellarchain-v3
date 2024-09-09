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
    private $likeRepository;

    public function __construct(ManagerRegistry $registry, LikeRepository $likeRepository)
    {
        parent::__construct($registry, Post::class);
        $this->likeRepository = $likeRepository;
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
            ->orderBy('p.rank', 'desc')
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

    public function getLikesCount(Post $post): int
    {
        return $this->likeRepository->countLikes($this->getClassMetadata()->getTableName(), $post->getId());
    }
}
