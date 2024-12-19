<?php

namespace App\Repository;

use App\Entity\AggregatedMetrics;
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
}
