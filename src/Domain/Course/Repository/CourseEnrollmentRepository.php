<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseEnrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourseEnrollmentRepository extends ServiceEntityRepository
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
}
