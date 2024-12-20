<?php

namespace App\Repository\Horizon;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @extends ServiceEntityRepository<HistoryTransaction>
 */
class HistoryTransactionsRepository extends EntityRepository
{
    public function findByLedgerIds(array $ledgerIds): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.id')
            ->where('t.ledger_sequence IN (:ledgerIds)')
            ->setParameter('ledgerIds', $ledgerIds)
            ->getQuery()
            ->getResult();
    }

    public function getTotalFees(array $ledgerIds): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $ids = implode(',', $ledgerIds);

        $sql = "
            SELECT SUM(public.history_transactions.fee_charged) AS total_fee_charged,
            SUM(public.history_transactions.max_fee) AS total_max_fee
            FROM public.history_transactions
            WHERE public.history_transactions.ledger_sequence IN ($ids)
        ";

        $result = $connection->executeQuery($sql)->fetchAssociative();

        return [
            'total_fee_charged' => isset($result['total_fee_charged']) ? (float)$result['total_fee_charged'] : 0.0,
            'total_max_fee' => isset($result['total_max_fee']) ? (float)$result['total_max_fee'] : 0.0,
        ];
    }

}
