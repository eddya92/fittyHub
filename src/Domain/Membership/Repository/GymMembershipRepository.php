<?php

namespace App\Domain\Membership\Repository;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GymMembership>
 */
class GymMembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymMembership::class);
    }

    /**
     * Find active memberships for a user
     *
     * @return GymMembership[]
     */
    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('gm')
            ->andWhere('gm.user = :user')
            ->andWhere('gm.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active memberships for a gym
     *
     * @return GymMembership[]
     */
    public function findActiveByGym(Gym $gym): array
    {
        return $this->createQueryBuilder('gm')
            ->andWhere('gm.gym = :gym')
            ->andWhere('gm.status = :status')
            ->setParameter('gym', $gym)
            ->setParameter('status', 'active')
            ->orderBy('gm.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find expiring memberships (within next 7 days)
     *
     * @return GymMembership[]
     */
    public function findExpiringMemberships(): array
    {
        $now = new \DateTime();
        $sevenDaysLater = (new \DateTime())->modify('+7 days');

        return $this->createQueryBuilder('gm')
            ->andWhere('gm.status = :status')
            ->andWhere('gm.endDate BETWEEN :now AND :sevenDays')
            ->setParameter('status', 'active')
            ->setParameter('now', $now)
            ->setParameter('sevenDays', $sevenDaysLater)
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if user has active membership for a gym
     */
    public function hasActiveMembership(User $user, Gym $gym): bool
    {
        $count = $this->createQueryBuilder('gm')
            ->select('COUNT(gm.id)')
            ->andWhere('gm.user = :user')
            ->andWhere('gm.gym = :gym')
            ->andWhere('gm.status = :status')
            ->setParameter('user', $user)
            ->setParameter('gym', $gym)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Persiste una membership
     */
    public function save(GymMembership $membership, bool $flush = false): void
    {
        $this->getEntityManager()->persist($membership);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Rimuove una membership
     */
    public function remove(GymMembership $membership, bool $flush = false): void
    {
        $this->getEntityManager()->remove($membership);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trova memberships con filtri dinamici
     */
    public function findWithFilters(?string $status = null, ?string $search = null, ?int $gymId = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.user', 'u')
            ->leftJoin('m.gym', 'g')
            ->orderBy('m.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('m.status = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($gymId) {
            $qb->andWhere('g.id = :gym')
               ->setParameter('gym', $gymId);
        }

        return $qb->getQuery()->getResult();
    }
}
