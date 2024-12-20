<?php

namespace App\Repository;

use App\Entity\AggregatedMetrics;
use App\Config\Timeframes;
use App\Config\Metric;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AggregatedMetrics>
 */
class AggregatedMetricsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AggregatedMetrics::class);
    }

    public function findMetricsAfterTimestamp(string $key, string $time, int $startTime, int $limit = 50): array
    {
        $timeframe = Timeframes::fromString($time)->value;
        $metric = Metric::fromString($key)->value;
        $startDateTime = (new \DateTimeImmutable())->setTimestamp($startTime);
        $qb = $this->createQueryBuilder('m')
            ->where('m.metric_id = :key')
            ->andWhere('m.timeframe = :timeframe')
            ->andWhere('m.created_at <= :startTime')
            ->setParameter('key', $metric)
            ->setParameter('timeframe', $timeframe)
            ->setParameter('startTime', $startDateTime)
            ->orderBy('m.created_at', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findMetricsBetweenTimestamp(int $key, DateTimeInterface $startDateTime, DateTimeInterface $endDateTime, int $timeframe = 0): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.metric_id = :key')
            ->andWhere('m.created_at <= :startTime')
            ->andWhere('m.created_at >= :endTime')
            ->andWhere('m.timeframe = :timeFrame')
            ->setParameter('key', $key)
            ->setParameter('startTime', $startDateTime)
            ->setParameter('timeFrame', $timeframe)
            ->setParameter('endTime', $endDateTime)
            ->orderBy('m.created_at', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findFirstMetricTimestamp(string $key, int $timeFrame = 0): ?DateTimeInterface
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.created_at')
            ->where('m.metric_id = :key')
            ->andWhere('m.timeframe = :timeFrame')
            ->setParameter('key', $key)
            ->setParameter('timeFrame', $timeFrame)
            ->orderBy('m.created_at', 'ASC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result) {
            return $result['created_at'];
        }

        return null;
    }

    public function findLastMetricTimestamp(string $key, int $timeFrame = 0): ?DateTimeInterface
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.created_at')
            ->where('m.metric_id = :key')
            ->andWhere('m.timeframe = :timeFrame')
            ->setParameter('key', $key)
            ->setParameter('timeFrame', $timeFrame)
            ->orderBy('m.created_at', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result) {
            return $result['created_at'];
        }

        return null;
    }

    public function findPreviousMetricTimestamp(string $key, \DateTimeInterface $currentTimestamp, int $timeFrame = 0): ?DateTimeInterface
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.created_at')
            ->where('m.metric_id = :key')
            ->andWhere('m.created_at < :currentTimestamp')
            ->andWhere('m.timeframe = :timeFrame')
            ->setParameter('key', $key)
            ->setParameter('currentTimestamp', $currentTimestamp)
            ->setParameter('timeFrame', $timeFrame)
            ->orderBy('m.created_at', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result) {
            return $result['created_at'];
        }

        return null;
    }
}
