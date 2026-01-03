<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Workout\Entity\Exercise;
use App\Domain\Workout\Repository\ExerciseRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineExerciseRepository extends ServiceEntityRepository implements ExerciseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercise::class);
    }

    /**
     * Find all active exercises
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('e.category', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find exercises by category
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.category = :category')
            ->andWhere('e.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search exercises by name
     */
    public function searchByName(string $search): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.name LIKE :search')
            ->andWhere('e.isActive = :active')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('active', true)
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find exercises by muscle group
     */
    public function findByMuscleGroup(string $muscleGroup): array
    {
        return $this->createQueryBuilder('e')
            ->where('JSON_CONTAINS(e.muscleGroups, :muscleGroup) = 1')
            ->andWhere('e.isActive = :active')
            ->setParameter('muscleGroup', json_encode($muscleGroup))
            ->setParameter('active', true)
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all exercise categories
     */
    public function getAllCategories(): array
    {
        $result = $this->createQueryBuilder('e')
            ->select('DISTINCT e.category')
            ->where('e.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('e.category', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($result, 'category');
    }
}
