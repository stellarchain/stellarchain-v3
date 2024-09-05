<?php

namespace App\Repository;

use App\Entity\LedgerStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LedgerStat>
 */
class LedgerStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LedgerStat::class);
    }

    /**
     * Retrieves LedgerStat records between two dates.
     *
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     * @return LedgerStat[] Returns an array of LedgerStat objects
     */
    public function getBetweenDates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.created_at >= :startDate')
            ->andWhere('l.created_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('l.created_at', 'ASC')  // Order by date (optional)
            ->getQuery()
            ->getResult();
    }
}
