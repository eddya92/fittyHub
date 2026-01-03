<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Workout\Entity\WorkoutExercise;
use App\Domain\Workout\Repository\WorkoutExerciseRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkoutExercise>
 */
class DoctrineWorkoutExerciseRepository extends ServiceEntityRepository implements WorkoutExerciseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkoutExercise::class);
    }
}
