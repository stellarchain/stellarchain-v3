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
}
