<?php

namespace App\Repository\Horizon;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @extends ServiceEntityRepository<HistoryLedgers>
 */
class HistoryTradesRepository extends EntityRepository
{
    public function totalTrades($start, $end): int
    {
        $queryBuilder = $this->createQueryBuilder('ht')
            ->select('COUNT(ht.history_operation_id) as total_trades')
            ->where('ht.ledger_closed_at >= :start_time')
            ->andWhere('ht.ledger_closed_at < :end_time')
            ->setParameter(
                'start_time', $start,
            )
            ->setParameter(
                'end_time', $end,
            );

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getTradeStats(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $qb = $this->createQueryBuilder('ht')
            ->select('ha.asset_type', 'ha.asset_code', 'ha.asset_issuer', 'SUM(ht.base_amount) * 0.0000001 AS volume')
            ->innerJoin('App\Entity\Horizon\HistoryAssets', 'ha', 'WITH', 'ht.base_asset_id = ha.id')
            ->where('ht.ledger_closed_at > :start_time')
            ->andWhere('ht.ledger_closed_at < :end_time')
            ->groupBy('ha.asset_type', 'ha.asset_code', 'ha.asset_issuer')
            ->setParameter('start_time', $start)
            ->setParameter('end_time', $end);

        return $qb->getQuery()->getResult();
    }
}
