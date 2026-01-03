<?php

namespace App\Domain\Workout\Repository;

use App\Domain\Workout\Entity\WorkoutSession;
use App\Domain\User\Entity\User;

/**
 * Repository interface per WorkoutSession
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface WorkoutSessionRepositoryInterface
{
    /**
     * Find recent sessions for a client
     *
     * @return WorkoutSession[]
     */
    public function findRecentByClient(User $client, int $limit = 10): array;
}
