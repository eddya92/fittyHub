<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Implementazione Doctrine del CourseEnrollmentRepository
 */
class DoctrineCourseEnrollmentRepository extends ServiceEntityRepository implements CourseEnrollmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseEnrollment::class);
    }

    public function save(CourseEnrollment $enrollment, bool $flush = false): void
    {
        $this->getEntityManager()->persist($enrollment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseEnrollment $enrollment, bool $flush = false): void
    {
        $this->getEntityManager()->remove($enrollment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveEnrollmentForSchedule(User $user, CourseSchedule $schedule): ?CourseEnrollment
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.schedule = :schedule')
            ->andWhere('e.status = :status')
            ->setParameter('user', $user)
            ->setParameter('schedule', $schedule)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveEnrollmentsByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->orderBy('e.enrolledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveEnrollmentsBySchedule(CourseSchedule $schedule): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.schedule = :schedule')
            ->andWhere('e.status = :status')
            ->setParameter('schedule', $schedule)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?CourseEnrollment
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    public function findActiveEnrollmentForUserAndSession(User $user, CourseSession $session): ?CourseEnrollment
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.session = :session')
            ->andWhere('e.status = :status')
            ->setParameter('user', $user)
            ->setParameter('session', $session)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
