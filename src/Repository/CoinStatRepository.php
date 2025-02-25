<?php

namespace App\Repository;

use App\Entity\CoinStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @extends ServiceEntityRepository<CoinStat>
 */
class CoinStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoinStat::class);
    }

    /**
     * @return mixed[]
     * @param array<int,mixed> $requiredStats
     */
    public function findLatestAndPreviousBySymbol(string $symbol, array $requiredStats): array
    {
        $sql = "
            SELECT name, value, prev_value FROM (
                SELECT
                    name,
                    value,
                    LEAD(value) OVER (PARTITION BY name ORDER BY created_at DESC) AS prev_value,
                    ROW_NUMBER() OVER (PARTITION BY name ORDER BY created_at DESC) AS row_num
                FROM coin_stat
                WHERE name IN (:names) AND coin_id = (
                    SELECT id FROM coin WHERE symbol = :symbol
                )
            ) AS sub
            WHERE row_num = 1;
        ";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('value', 'value');
        $rsm->addScalarResult('prev_value', 'prev_value');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('names', $requiredStats);
        $query->setParameter('symbol', $symbol);

        return $query->getResult();
    }

    public function getStatsByName(string $name, $offset = 0, int $limit = 25): array
    {
        $sql = "
            SELECT
                created_at,
                value
            FROM coin_stat
            WHERE name = :name
            ORDER BY created_at DESC LIMIT :limit OFFSET :offset;
        ";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('created_at', 'created_at');
        $rsm->addScalarResult('value', 'value');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('name', $name);

        $query->setParameter('limit', $limit);
        $query->setParameter('offset', $offset);

        return $query->getResult();
    }

    public function getTotalCountByName(string $name): int
    {
        $sql = "
            SELECT
                COUNT(*) AS total_count
            FROM coin_stat
            WHERE name = :name;
        ";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total_count', 'total_count');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $query->setParameter('name', $name);

        $result = $query->getSingleScalarResult();

        return (int)$result;
    }
}
