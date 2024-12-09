<?php

namespace App\Repository\Horizon;

use App\Entity\Horizon\Accounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;

/**
 * @extends ServiceEntityRepository<Accounts>
 */
class AccountsRepository extends EntityRepository
{
    public function totalAccounts(): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.account_id) AS total');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function averageBalanceAccounts(int $topAccounts = 100): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.balance')
            ->where('a.balance != 0')
            ->orderBy('a.balance', 'DESC')
            ->setMaxResults($topAccounts);

        $balances = $qb->getQuery()->getResult();
        $sum = 0;
        $count = count($balances);

        foreach ($balances as $account) {
            $sum = bcadd($sum, $account['balance']);
        }

        if ($count === 0) {
            return 0;
        }

        $averageBalance = bcdiv($sum, $count);

        return (int) $averageBalance;
    }

    public function activeAddressesCount(): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.account_id) AS active_count')
            ->where('a.balance != 0');

        $result = $qb->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    public function inactiveAddressesCount(): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.account_id) AS inactive_count')
            ->where('a.balance = 0');

        $result = $qb->getQuery()->getSingleScalarResult();

        return (int) $result;
    }
}
