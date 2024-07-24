<?php

namespace App\Repository;

use App\Entity\SCF\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Round>
 */
class RoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Round::class);
    }

    public function findAllRoundsWithLimitedProjects(int $maxProjects = 30): mixed
    {
        $rounds = $this->createQueryBuilder('r')
            ->getQuery()
            ->getResult();

        $em = $this->getEntityManager();

        foreach ($rounds as $round) {
            $totalProjects = $em->createQueryBuilder()
                ->select('COUNT(p.id)')
                ->from('App\Entity\Project', 'p')
                ->where('p.round = :round')
                ->setParameter('round', $round)
                ->getQuery()
                ->getSingleScalarResult();

            $round->setTotalProjectCount((int) $totalProjects);

            $projects = $em->createQueryBuilder()
                ->select('p')
                ->from('App\Entity\Project', 'p')
                ->where('p.round = :round')
                ->setParameter('round', $round)
                ->setMaxResults($maxProjects)
                ->getQuery()
                ->getResult();

            // Clear and set limited projects
            $round->getProjects()->clear();
            foreach ($projects as $project) {
                $round->getProjects()->add($project);
            }
        }

        return $rounds;
    }
}
