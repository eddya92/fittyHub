<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Repository\CourseRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Implementazione Doctrine del CourseRepository
 */
class DoctrineCourseRepository extends ServiceEntityRepository implements CourseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymCourse::class);
    }

    public function findWithFilters(?string $search, ?string $category, ?string $status): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.gym', 'g')
            ->leftJoin('c.instructor', 'i')
            ->orderBy('c.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $qb->andWhere('c.category = :category')
               ->setParameter('category', $category);
        }

        if ($status) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findActiveWithSchedules(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.schedules', 's')
            ->addSelect('s')
            ->leftJoin('c.category', 'cat')
            ->addSelect('cat')
            ->leftJoin('c.instructor', 'i')
            ->addSelect('i')
            ->where('c.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
