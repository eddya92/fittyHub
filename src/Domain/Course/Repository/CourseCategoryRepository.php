<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourseCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseCategory::class);
    }

    public function save(CourseCategory $category, bool $flush = false): void
    {
        $this->getEntityManager()->persist($category);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseCategory $category, bool $flush = false): void
    {
        $this->getEntityManager()->remove($category);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
