<?php

namespace App\Domain\Workout\Repository;

use App\Domain\Workout\Entity\WorkoutExercise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkoutExercise>
 */
class WorkoutExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkoutExercise::class);
    }
}
