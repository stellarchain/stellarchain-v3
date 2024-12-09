<?php

namespace App\Repository\Horizon;

use App\Entity\Horizon\HistoryOperations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @extends ServiceEntityRepository<HistoryOperations>
 */
class HistoryOperationsRepository extends EntityRepository
{
    /**
     * Get the total output value from the history_operations table.
     *
     * @param array $transactionIds
     * @return float
     */
    public function getTotalOutput(array $transactionIds): float
    {
        $connection = $this->getEntityManager()->getConnection();
        $ids = implode(',', $transactionIds);

        $sql = "
            SELECT CAST(SUM(CAST(public.history_operations.details->>'amount' AS DOUBLE PRECISION)) AS NUMERIC(20, 8)) AS output_value
            FROM public.history_operations
            WHERE public.history_operations.type IN (1, 2, 13)
            AND public.history_operations.transaction_id IN ($ids)
        ";

        $result = $connection->executeQuery($sql)->fetchAssociative();

        return isset($result['output_value']) ? (float)$result['output_value'] : 0.0;
    }

    public function getXmlPayments(array $transactionIds): float
    {
        $connection = $this->getEntityManager()->getConnection();
        $ids = implode(',', $transactionIds);

        $sql = "
            SELECT CAST(SUM(CAST(public.history_operations.details->>'amount' AS DOUBLE PRECISION)) AS NUMERIC(20,8)) AS output_value
            FROM public.history_operations
            WHERE public.history_operations.type IN (1, 2, 13)
            AND public.history_operations.transaction_id IN ($ids)
            AND public.history_operations.details->>'asset_type' = 'native'
        ";

        $result = $connection->executeQuery($sql)->fetchAssociative();

        return isset($result['output_value']) ? (float)$result['output_value'] : 0.0;
    }

    public function getTotalPayments(array $transactionIds): float
    {
        $connection = $this->getEntityManager()->getConnection();
        $ids = implode(',', $transactionIds);

        $sql = "
            SELECT CAST(SUM(CAST(public.history_operations.details->>'amount' AS DOUBLE PRECISION)) AS NUMERIC(20,8)) AS output_value
            FROM public.history_operations
            WHERE public.history_operations.type IN (0, 1, 2, 8, 13)
            AND public.history_operations.transaction_id IN ($ids)
        ";

        $result = $connection->executeQuery($sql)->fetchAssociative();

        return isset($result['output_value']) ? (float)$result['output_value'] : 0.0;
    }

    private function getCreateAccountOperations($transactions)
    {
        $ids = array_column($transactions, 'id');
        $ids = array_map('intval', $ids);

        $qb = $this->doctrine->getConnection('horizon')->createQueryBuilder();

        $qb->select("SUM(CAST(public.history_operations.details->>'starting_balance' AS numeric)) AS output_value")
            ->from('public.history_operations')
            ->where('public.history_operations.type = 0')
            ->andWhere("public.history_operations.details.details->>'asset_type' = 'native'")
            ->andWhere($qb->expr()->in('transaction_id', ':ids'))
            ->setParameter('ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        $result = $qb->executeQuery()->fetchAllAssociative();

        return $result;
    }
}
