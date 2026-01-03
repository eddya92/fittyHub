<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseWaitingList;
use App\Domain\Course\Repository\CourseWaitingListRepositoryInterface;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineCourseWaitingListRepository extends ServiceEntityRepository implements CourseWaitingListRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseWaitingList::class);
    }

    public function save(CourseWaitingList $waitingList, bool $flush = false): void
    {
        $this->getEntityManager()->persist($waitingList);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseWaitingList $waitingList, bool $flush = false): void
    {
        $this->getEntityManager()->remove($waitingList);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveWaitingForSchedule(User $user, CourseSchedule $schedule): ?CourseWaitingList
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.schedule = :schedule')
            ->andWhere('w.status = :status')
            ->setParameter('user', $user)
            ->setParameter('schedule', $schedule)
            ->setParameter('status', 'waiting')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWaitingBySchedule(CourseSchedule $schedule): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.schedule = :schedule')
            ->andWhere('w.status = :status')
            ->setParameter('schedule', $schedule)
            ->setParameter('status', 'waiting')
            ->orderBy('w.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countWaitingBySchedule(CourseSchedule $schedule): int
    {
        return (int) $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.schedule = :schedule')
            ->andWhere('w.status = :status')
            ->setParameter('schedule', $schedule)
            ->setParameter('status', 'waiting')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNextPositionForSchedule(CourseSchedule $schedule): int
    {
        $maxPosition = $this->createQueryBuilder('w')
            ->select('MAX(w.position)')
            ->where('w.schedule = :schedule')
            ->andWhere('w.status = :status')
            ->setParameter('schedule', $schedule)
            ->setParameter('status', 'waiting')
            ->getQuery()
            ->getSingleScalarResult();

        return ($maxPosition ?? 0) + 1;
    }
}