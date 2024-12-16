<?php

namespace App\Repository\Horizon;

use App\Entity\Horizon\HistoryAssets;
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
                'start_time',
                $start,
            )
            ->setParameter(
                'end_time',
                $end,
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

    public function findSumByAssets(HistoryAssets $baseAsset, HistoryAssets $counterAsset, \DateTime $interval): ?array
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.base_amount) as counterAmount', 'SUM(t.counter_amount) as baseAmount')
            ->where('t.base_asset_id = :baseAsset')
            ->andWhere('t.counter_asset_id = :counterAsset')
            ->andWhere('t.ledger_closed_at >= :timeLimit')
            ->setParameter('timeLimit', $interval)
            ->setParameter('baseAsset', $baseAsset->getId())
            ->setParameter('counterAsset', $counterAsset->getId())
            ->getQuery()
            ->getSingleResult();
    }

    public function countTotalTrades(HistoryAssets $baseAsset, HistoryAssets $counterAsset, \DateTime $interval): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.history_operation_id) as totalTrades')
            ->where('t.base_asset_id = :baseAsset')
            ->andWhere('t.counter_asset_id = :counterAsset')
            ->andWhere('t.ledger_closed_at >= :timeLimit')
            ->setParameter('timeLimit', $interval)
            ->setParameter('baseAsset', $baseAsset->getId())
            ->setParameter('counterAsset', $counterAsset->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPriceAt(HistoryAssets $baseAsset, HistoryAssets $counterAsset, \DateTime $interval): ?float
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.price_n', 't.price_d', 't.ledger_closed_at')
            ->where('t.base_asset_id = :baseAsset')
            ->andWhere('t.counter_asset_id = :counterAsset')
            ->andWhere('t.ledger_closed_at >= :timeLimit')
            ->setParameter('timeLimit', $interval)
            ->setParameter('baseAsset', $baseAsset->getId())
            ->setParameter('counterAsset', $counterAsset->getId())
            ->orderBy('t.ledger_closed_at', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result) {
            return null;
        }

        return $result['price_n'] / $result['price_d'];
    }
}
