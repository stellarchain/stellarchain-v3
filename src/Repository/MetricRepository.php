<?php

namespace App\Repository;

use App\Config\Timeframes;
use App\Entity\Metric;
use DateTimeInterface;
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

    public function findMetricsBetweenTimestamp(string $key, DateTimeInterface $startDateTime, DateTimeInterface $endDateTime): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.metric = :key')
            ->andWhere('m.timestamp <= :startTime')
            ->andWhere('m.timestamp >= :endTime')
            ->setParameter('key', $key)
            ->setParameter('startTime', $startDateTime)
            ->setParameter('endTime', $endDateTime)
            ->orderBy('m.timestamp', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findFirstMetricTimestamp(string $key): ?DateTimeInterface
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.timestamp')
            ->where('m.metric = :key')
            ->setParameter('key', $key)
            ->orderBy('m.timestamp', 'ASC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result) {
            return $result['timestamp'];
        }

        return null;
    }

    public function findLastMetricTimestamp(string $key): ?DateTimeInterface
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.timestamp')
            ->where('m.metric = :key')
            ->setParameter('key', $key)
            ->orderBy('m.timestamp', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result) {
            return $result['timestamp'];
        }

        return null;
    }

    public function findPreviousMetricTimestamp(string $key, \DateTimeInterface $currentTimestamp): ?DateTimeInterface
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.timestamp')
            ->where('m.metric = :key')
            ->andWhere('m.timestamp < :currentTimestamp')
            ->setParameter('key', $key)
            ->setParameter('currentTimestamp', $currentTimestamp)
            ->orderBy('m.timestamp', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result) {
            return $result['timestamp'];
        }

        return null;
    }
}
