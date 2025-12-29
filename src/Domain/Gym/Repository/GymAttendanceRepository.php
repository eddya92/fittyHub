<?php

namespace App\Domain\Gym\Repository;

use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GymAttendance>
 */
class GymAttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymAttendance::class);
    }

    /**
     * Find attendances for a gym in a date range
     *
     * @return GymAttendance[]
     */
    public function findByGymAndDateRange(Gym $gym, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('ga')
            ->andWhere('ga.gym = :gym')
            ->andWhere('ga.checkInTime BETWEEN :startDate AND :endDate')
            ->setParameter('gym', $gym)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('ga.checkInTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find attendances for a user
     *
     * @return GymAttendance[]
     */
    public function findByUser(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('ga')
            ->andWhere('ga.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ga.checkInTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count attendances for gym in current month
     */
    public function countCurrentMonthAttendances(Gym $gym): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');

        return $this->createQueryBuilder('ga')
            ->select('COUNT(ga.id)')
            ->andWhere('ga.gym = :gym')
            ->andWhere('ga.checkInTime BETWEEN :start AND :end')
            ->setParameter('gym', $gym)
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
