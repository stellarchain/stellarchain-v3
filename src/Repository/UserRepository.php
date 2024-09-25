<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function isFollowingProject(User $user, Project $project): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(f)')
            ->join('u.followedProjects', 'f')
            ->where('u.id = :userId')
            ->andWhere('f.id = :projectId')
            ->setParameter('userId', $user->getId())
            ->setParameter('projectId', $project->getId());

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findTopUsersByPostCount(int $limit): array
    {
        return $this->createQueryBuilder('u')
            ->select('u, COUNT(p.id) as postCount')
            ->leftJoin('u.communityPosts', 'p')
            ->groupBy('u.id')
            ->orderBy('postCount', 'DESC')
            ->having('COUNT(p.id) > 0')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUniqueFollowers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(DISTINCT f.id)')
            ->leftJoin('u.followedCommunities', 'f') // Assuming there's a followers relationship
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isFollowing($currentUser, $targetUser): bool
    {
        return $currentUser && $currentUser->getFollowedUsers()->contains($targetUser);
    }
}
