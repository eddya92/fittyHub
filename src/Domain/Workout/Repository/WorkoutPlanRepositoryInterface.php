<?php

namespace App\Domain\Workout\Repository;

use App\Domain\Workout\Entity\WorkoutPlan;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;

/**
 * Repository interface per WorkoutPlan
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface WorkoutPlanRepositoryInterface
{
    /**
     * Find active plans for a client
     *
     * @return WorkoutPlan[]
     */
    public function findActiveByClient(User $client): array;

    /**
     * Find plans created by a PT
     *
     * @return WorkoutPlan[]
     */
    public function findByPersonalTrainer(PersonalTrainer $pt): array;

    /**
     * Find template plans created by a PT
     *
     * @return WorkoutPlan[]
     */
    public function findTemplatesByPT(PersonalTrainer $pt): array;
}
