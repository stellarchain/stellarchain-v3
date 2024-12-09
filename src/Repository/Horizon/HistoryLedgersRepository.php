<?php

namespace App\Repository\Horizon;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @extends ServiceEntityRepository<HistoryLedgers>
 */
class HistoryLedgersRepository extends EntityRepository
{

    public function findByClosedAtRange(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $queryBuilder = $this->createQueryBuilder('hl')
            ->where('hl.closed_at >= :start_time')
            ->andWhere('hl.closed_at < :end_time')
            ->setParameter(
                'start_time', $start->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            )
            ->setParameter(
                'end_time', $end->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            )->orderBy('hl.sequence', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}
