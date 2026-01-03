<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Implementazione Doctrine del CourseScheduleRepository
 */
class DoctrineCourseScheduleRepository extends ServiceEntityRepository implements CourseScheduleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseSchedule::class);
    }

    public function save(CourseSchedule $schedule, bool $flush = false): void
    {
        $this->getEntityManager()->persist($schedule);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseSchedule $schedule, bool $flush = false): void
    {
        $this->getEntityManager()->remove($schedule);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll(): array
    {
        return parent::findAll();
    }
}
