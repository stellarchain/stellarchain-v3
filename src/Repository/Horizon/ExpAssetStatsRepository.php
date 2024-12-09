<?php

namespace App\Repository\Horizon;

use App\Entity\Horizon\ExpAssetStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @extends ServiceEntityRepository<ExpAssetStats>
 */
class ExpAssetStatsRepository extends EntityRepository
{
    public function totalAssets(): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(asset_code) AS total_assets FROM public.exp_asset_stats";

        $result = $connection->executeQuery($sql)->fetchAssociative();

        return (int) $result['total_assets'];
    }
}
