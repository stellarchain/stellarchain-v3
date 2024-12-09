<?php

namespace App\Repository\Horizon;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Offers>
 */
class OffersRepository extends EntityRepository
{
    /**
     * Get offers for specific selling and buying assets with the minimum price
     *
     * @param array $xdrAssets
     * @param int $nativeXdr
     * @return array
     */
    public function getOffersByAssets(array $xdrAssets, string $nativeXdr): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $quotedAssets = array_map(function ($asset) {
            return "'" . addslashes($asset) . "'";
        }, $xdrAssets);
        $assets = implode(',', $quotedAssets);
        $quotedNativeXdr = "'" . addslashes($nativeXdr) . "'";

        $sql = "
            SELECT selling_asset, MIN(price) AS price
            FROM public.offers
            WHERE selling_asset IN ($assets)
            AND buying_asset = $quotedNativeXdr
            AND deleted = false
            GROUP BY selling_asset
        ";

        return $connection->executeQuery($sql)->fetchAssociative();
    }
}
