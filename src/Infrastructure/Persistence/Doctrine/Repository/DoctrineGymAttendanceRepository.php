<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\Course\Entity\CourseSession;
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

    public function save(GymAttendance $attendance, bool $flush = false): void
    {
        $this->getEntityManager()->persist($attendance);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserAndSession(User $user, CourseSession $session): ?GymAttendance
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.courseSession = :session')
            ->andWhere('a.type = :type')
            ->setParameter('user', $user)
            ->setParameter('session', $session)
            ->setParameter('type', 'course')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySession(CourseSession $session): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.courseSession = :session')
            ->andWhere('a.type = :type')
            ->setParameter('session', $session)
            ->setParameter('type', 'course')
            ->orderBy('a.checkInTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getAttendanceStatsByCourse(int $courseId, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        // Query per contare sessioni totali del corso
        $qb1 = $this->getEntityManager()->createQueryBuilder();
        $qb1->select('COUNT(DISTINCT s.id)')
            ->from(CourseSession::class, 's')
            ->where('s.course = :courseId')
            ->setParameter('courseId', $courseId);

        if ($from) {
            $qb1->andWhere('s.sessionDate >= :from')
                ->setParameter('from', $from);
        }

        if ($to) {
            $qb1->andWhere('s.sessionDate <= :to')
                ->setParameter('to', $to);
        }

        $totalSessions = (int) $qb1->getQuery()->getSingleScalarResult();

        // Query per statistiche presenze
        $qb2 = $this->createQueryBuilder('a');
        $qb2->select('COUNT(a.id) as total_attendances')
            ->addSelect('COUNT(DISTINCT a.user) as unique_users')
            ->join('a.courseSession', 's')
            ->where('s.course = :courseId')
            ->andWhere('a.type = :type')
            ->setParameter('courseId', $courseId)
            ->setParameter('type', 'course');

        if ($from) {
            $qb2->andWhere('s.sessionDate >= :from')
                ->setParameter('from', $from);
        }

        if ($to) {
            $qb2->andWhere('s.sessionDate <= :to')
                ->setParameter('to', $to);
        }

        $result = $qb2->getQuery()->getSingleResult();

        $totalAttendances = (int) $result['total_attendances'];
        $uniqueUsers = (int) $result['unique_users'];

        // Calcola tasso medio di partecipazione
        $averageRate = 0;
        if ($totalSessions > 0 && $uniqueUsers > 0) {
            $averageRate = round(($totalAttendances / ($totalSessions * $uniqueUsers)) * 100, 2);
        }

        return [
            'total_sessions' => $totalSessions,
            'total_attendances' => $totalAttendances,
            'unique_users' => $uniqueUsers,
            'average_rate' => $averageRate
        ];
    }
}
