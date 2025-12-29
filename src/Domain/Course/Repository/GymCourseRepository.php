<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\GymCourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GymCourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymCourse::class);
    }

    public function save(GymCourse $course, bool $flush = false): void
    {
        $this->getEntityManager()->persist($course);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GymCourse $course, bool $flush = false): void
    {
        $this->getEntityManager()->remove($course);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trova corsi con filtri (search, category, status)
     */
    public function findWithFilters(?string $search = null, ?string $category = null, ?string $status = null): array
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

    /**
     * Trova corsi attivi con schedule e category per il calendario
     */
    public function findActiveWithSchedules(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.schedules', 's')
            ->leftJoin('c.instructor', 'i')
            ->leftJoin('i.user', 'u')
            ->leftJoin('c.category', 'cat')
            ->where('c.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Conta corsi per status
     */
    public function countByStatus(string $status): int
    {
        return $this->count(['status' => $status]);
    }
}
