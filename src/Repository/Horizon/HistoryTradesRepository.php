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

    public function findSumByAssets(HistoryAssets $baseAsset, HistoryAssets $counterAsset, \DateTime $interval, $reversed = false): ?array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('SUM(t.counter_amount) as baseAmount', 'SUM(t.base_amount) as counterAmount');

        $qb->where('t.base_asset_id = :baseAsset')
            ->andWhere('t.counter_asset_id = :counterAsset')
            ->andWhere('t.ledger_closed_at >= :timeLimit')
            ->setParameter('timeLimit', $interval)
            ->setParameter('baseAsset', $baseAsset->getId())
            ->setParameter('counterAsset', $counterAsset->getId());

        $result = $qb->getQuery()->getSingleResult();

        return $result;
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

    public function getPriceAt(HistoryAssets $baseAsset, HistoryAssets $counterAsset, \DateTime $interval, $reversed = false): ?float
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

        if ($reversed) {
            return $result['price_d'] / $result['price_n'];
        }

        return $result['price_n'] / $result['price_d'];
    }


    public function getLatestPrice($baseAsset, $counterAsset, $baseIsSeller)
    {
        $queryBuilder = $this->createQueryBuilder('ht');

        $queryBuilder->where(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('ht.base_asset_id', ':baseAsset'),
                    $queryBuilder->expr()->eq('ht.counter_asset_id', ':counterAsset'),
                    $queryBuilder->expr()->eq('ht.base_is_seller', ':baseIsSeller'),
                    $queryBuilder->expr()->eq('ht.trade_type', ':tradeType'),
                    $queryBuilder->expr()->eq('ht.base_is_exact', ':baseIsExact'),
                    $queryBuilder->expr()->eq('ht.order', ':order')
                ),
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('ht.base_asset_id', ':counterAsset'),
                    $queryBuilder->expr()->eq('ht.counter_asset_id', ':baseAsset'),
                    $queryBuilder->expr()->eq('ht.trade_type', ':tradeType'),
                    $queryBuilder->expr()->eq('ht.base_is_exact', ':baseIsExact'),
                    $queryBuilder->expr()->eq('ht.base_is_seller', ':baseIsSeller'),
                    $queryBuilder->expr()->eq('ht.order', ':order')
                )
            )
        )
            ->setParameter('counterAsset', $counterAsset->getId())
            ->setParameter('baseAsset', $baseAsset->getId())
            ->setParameter('baseIsExact', true)
            ->setParameter('tradeType', 1)
            ->setParameter('order', 1)
            ->setParameter('baseIsSeller', $baseIsSeller)
            ->orderBy('ht.ledger_closed_at', 'DESC')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
