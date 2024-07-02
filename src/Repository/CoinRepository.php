<?php

namespace App\Repository;

use App\Entity\Coin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Coin>
 */
class CoinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coin::class);
    }

    /*
    * @param string $value
    */
    public function findOneBySymbol($value): ?Coin
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.symbol = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
