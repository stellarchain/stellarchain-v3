<?php

namespace App\Repository\StellarHorizon;

use App\Entity\StellarHorizon\Asset;
use App\Entity\StellarHorizon\Trade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trade>
 */
class TradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trade::class);
    }

    public function findSumByAssets(Asset $baseAsset, Asset $counterAsset, \DateTime $interval): ?array
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.base_amount) as counterAmount', 'SUM(t.counter_amount) as baseAmount')
            ->where('t.base_asset = :baseAsset')
            ->andWhere('t.counter_asset = :counterAsset')
            ->andWhere('t.ledger_close_time >= :timeLimit')
            ->setParameter('timeLimit', $interval)
            ->setParameter('baseAsset', $counterAsset)
            ->setParameter('counterAsset', $baseAsset)
            ->getQuery()
            ->getSingleResult();
    }

    public function countTotalTrades(Asset $baseAsset, Asset $counterAsset, \DateTime $interval): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as totalTrades')
            ->where('t.base_asset = :baseAsset')
            ->andWhere('t.counter_asset = :counterAsset')
            ->andWhere('t.ledger_close_time >= :timeLimit')
            ->setParameter('timeLimit', $interval)
            ->setParameter('baseAsset', $counterAsset)
            ->setParameter('counterAsset', $baseAsset)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPriceAt(Asset $baseAsset, Asset $counterAsset, \DateTime $interval): ?float
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.price')
            ->where('t.base_asset = :baseAsset')
            ->andWhere('t.counter_asset = :counterAsset')
            ->andWhere('t.ledger_close_time >= :timeLimit')
            ->setParameter('timeLimit', $interval)
            ->setParameter('baseAsset', $counterAsset)
            ->setParameter('counterAsset', $baseAsset)
            ->orderBy('t.ledger_close_time', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            return null;
        }

        return (float) $result['price'];
    }
}
