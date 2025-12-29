<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourseScheduleRepository extends ServiceEntityRepository
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
}
