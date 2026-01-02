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
}
