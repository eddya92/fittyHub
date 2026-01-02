<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
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
}
