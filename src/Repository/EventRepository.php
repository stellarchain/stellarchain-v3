<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
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
