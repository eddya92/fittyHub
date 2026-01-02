<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineGymAttendanceRepository extends ServiceEntityRepository implements GymAttendanceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymAttendance::class);
    }

    public function findRecentByGym(Gym $gym, string $type = 'gym_entrance', int $limit = 20): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.gym = :gym')
            ->andWhere('a.type = :type')
            ->setParameter('gym', $gym)
            ->setParameter('type', $type)
            ->orderBy('a.checkInTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndGym(User $user, Gym $gym, int $limit = 50): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.gym = :gym')
            ->setParameter('user', $user)
            ->setParameter('gym', $gym)
            ->orderBy('a.checkInTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByGymAndDateRange(Gym $gym, ?\DateTime $from = null, ?\DateTime $to = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.gym = :gym')
            ->setParameter('gym', $gym);

        if ($from) {
            $qb->andWhere('a.checkInTime >= :from')
               ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('a.checkInTime <= :to')
               ->setParameter('to', $to);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countUniqueUsersByGymAndDateRange(Gym $gym, ?\DateTime $from = null, ?\DateTime $to = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.user)')
            ->where('a.gym = :gym')
            ->setParameter('gym', $gym);

        if ($from) {
            $qb->andWhere('a.checkInTime >= :from')
               ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('a.checkInTime <= :to')
               ->setParameter('to', $to);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
