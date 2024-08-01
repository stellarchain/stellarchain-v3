<?php

namespace App\Repository;

use App\Entity\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    /**
     * Get jobs based on criteria, sorting, and pagination.
     *
     * @param Criteria $criteria
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function findJobs(Criteria $criteria, array $orderBy = [], int $offset = 0, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('j');

        // Apply criteria to the query builder
        if ($criteria->getWhereExpression()) {
            $qb->addCriteria($criteria);
        }

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                if ($field === 'applied') {
                    $this->addApplicationCountSorting($qb, $direction);
                } else {
                    $qb->addOrderBy('j.' . $field, $direction);
                }
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Add sorting by application count to the QueryBuilder.
     *
     * @param QueryBuilder $qb
     * @param string $direction
     */
    private function addApplicationCountSorting(QueryBuilder $qb, string $direction): void
    {
        $qb->leftJoin('j.applications', 'a')
            ->addSelect('COUNT(a.id) as HIDDEN applied_count')
            ->groupBy('j.id')
            ->orderBy('applied_count', $direction);
    }

    /**
     * Count jobs based on criteria.
     *
     * @param Criteria $criteria
     * @return int
     */
    public function countByCriteria(Criteria $criteria): int
    {
        $qb = $this->createQueryBuilder('j');

        if ($criteria->getWhereExpression()) {
            $this->applyCriteriaToQueryBuilder($criteria, $qb);
        }

        $qb->select('COUNT(j.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Apply Criteria conditions to QueryBuilder.
     *
     * @param Criteria $criteria
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    private function applyCriteriaToQueryBuilder(Criteria $criteria, $qb): void
    {
        foreach ($criteria->getWhereExpression() as $expression) {
            $qb->andWhere($expression);
        }
    }

    public function findByNameLike(string $query): mixed
    {
         return $this->createQueryBuilder('p')
            ->where('LOWER(p.title) LIKE LOWER(:name)')
            ->setParameter('name', '%' . strtolower($query) . '%')
            ->getQuery()
            ->getResult();
    }
}
