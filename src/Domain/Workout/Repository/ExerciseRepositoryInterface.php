<?php

namespace App\Domain\Workout\Repository;

use App\Domain\Workout\Entity\Exercise;

/**
 * Repository interface per Exercise
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface ExerciseRepositoryInterface
{
    /**
     * Find all active exercises
     *
     * @return Exercise[]
     */
    public function findAllActive(): array;

    /**
     * Find exercises by category
     *
     * @return Exercise[]
     */
    public function findByCategory(string $category): array;

    /**
     * Search exercises by name
     *
     * @return Exercise[]
     */
    public function searchByName(string $search): array;

    /**
     * Find exercises by muscle group
     *
     * @return Exercise[]
     */
    public function findByMuscleGroup(string $muscleGroup): array;

    /**
     * Get all exercise categories
     *
     * @return string[]
     */
    public function getAllCategories(): array;
}
