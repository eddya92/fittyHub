<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineCourseSessionRepository extends ServiceEntityRepository implements CourseSessionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseSession::class);
    }

    public function save(CourseSession $session, bool $flush = false): void
    {
        $this->getEntityManager()->persist($session);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseSession $session, bool $flush = false): void
    {
        $this->getEntityManager()->remove($session);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findBetweenDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.sessionDate >= :startDate')
            ->andWhere('s.sessionDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.sessionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCourseAndDateRange(GymCourse $course, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.course = :course')
            ->andWhere('s.sessionDate >= :startDate')
            ->andWhere('s.sessionDate <= :endDate')
            ->setParameter('course', $course)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.sessionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByScheduleAndDate(CourseSchedule $schedule, \DateTimeInterface $date): ?CourseSession
    {
        // Normalizza la data a midnight per confronto
        $normalizedDate = clone $date;
        if ($normalizedDate instanceof \DateTime) {
            $normalizedDate->setTime(0, 0, 0);
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.schedule = :schedule')
            ->andWhere('s.sessionDate = :date')
            ->setParameter('schedule', $schedule)
            ->setParameter('date', $normalizedDate)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUpcoming(int $limit = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.sessionDate >= :today')
            ->andWhere('s.status = :status')
            ->setParameter('today', new \DateTime('today'))
            ->setParameter('status', 'scheduled')
            ->orderBy('s.sessionDate', 'ASC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findPast(int $limit = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.sessionDate < :today')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('s.sessionDate', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.sessionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function exists(CourseSchedule $schedule, \DateTimeInterface $date): bool
    {
        return $this->findByScheduleAndDate($schedule, $date) !== null;
    }

    public function findUpcomingSessions(\DateTimeInterface $startWindow, \DateTimeInterface $endWindow): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.schedule', 'sch')
            ->leftJoin('s.enrollments', 'e')
            ->andWhere('s.status = :status')
            ->andWhere('s.sessionDate >= :startWindow')
            ->andWhere('s.sessionDate <= :endWindow')
            ->setParameter('status', 'scheduled')
            ->setParameter('startWindow', $startWindow)
            ->setParameter('endWindow', $endWindow)
            ->orderBy('s.sessionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}