<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findProjectsWithLikes(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.projectLikes', 'l')
            ->andWhere('l.created_at >= :weekAgo')
            ->setParameter('weekAgo', new \DateTime('-1 week'))
            ->addSelect('p')
            ->groupBy('p.id')
            ->having('COUNT(l.id) > 0');

        return $qb->getQuery()->getResult();
    }

    public function findByNameLike(string $query): mixed
    {
         return $this->createQueryBuilder('p')
            ->where('LOWER(p.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . strtolower($query) . '%')
            ->getQuery()
            ->getResult();
    }
}
