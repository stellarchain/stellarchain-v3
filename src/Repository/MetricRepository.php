<?php

namespace App\Repository;

use App\Config\Timeframes;
use App\Entity\Metric;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Metric>
 */
class MetricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Metric::class);
    }

    public function findMetricsAfterTimestamp(string $key, string $chartType, string $timeframe, int $startTime, int $limit = 50): array
    {
        $timeframe = Timeframes::fromString($timeframe)->value;
        $startDateTime = (new \DateTimeImmutable())->setTimestamp($startTime);
        $qb = $this->createQueryBuilder('m')
            ->where('m.metric = :key')
            ->andWhere('m.timeframe = :timeframe')
            ->andWhere('m.timestamp <= :startTime')
            ->andWhere('m.chart_type >= :chartType')
            ->setParameter('key', $key)
            ->setParameter('timeframe', $timeframe)
            ->setParameter('chartType', $chartType)
            ->setParameter('startTime', $startDateTime)
            ->orderBy('m.timestamp', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
