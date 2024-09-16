<?php

namespace App\Repository\StellarHorizon;

use App\Entity\StellarHorizon\Asset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Asset>
 */
class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    /**
     * Find an asset by its type, code, and issuer.
     *
     * @param string $assetType
     * @param string $assetCode
     * @param string $assetIssuer
     * @return Asset|null
     */
    public function findAsset($assetType, $assetCode, $assetIssuer): ?Asset
    {
        $qb = $this->createQueryBuilder('a');

        if ($assetType !== null) {
            $qb->andWhere('a.asset_type = :assetType')
                ->setParameter('assetType', $assetType);
        } else {
            $qb->andWhere('a.asset_type IS NULL');
        }

        if ($assetCode !== null) {
            $qb->andWhere('a.asset_code = :assetCode')
                ->setParameter('assetCode', $assetCode);
        } else {
            $qb->andWhere('a.asset_code IS NULL');
        }

        if ($assetIssuer !== null) {
            $qb->andWhere('a.asset_issuer = :assetIssuer')
                ->setParameter('assetIssuer', $assetIssuer);
        } else {
            $qb->andWhere('a.asset_issuer IS NULL');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByWithMetrics(array $filterCriteria, array $sortCriteria, int $limit, int $offset)
    {
        // Custom query to join the latest metric and sort by the given sort criteria
        return $this->createQueryBuilder('asset')
            ->leftJoin('asset.latestMetric', 'latestMetric')  // Assuming there's a relation for latestMetric
            ->where($filterCriteria)  // Apply filter criteria
            ->orderBy($sortCriteria)  // Apply sort criteria
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
