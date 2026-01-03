<?php

namespace App\Domain\Workout\Repository;

use App\Domain\Workout\Entity\ClientAssessment;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;

/**
 * Repository interface per ClientAssessment
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface ClientAssessmentRepositoryInterface
{
    /**
     * Find assessments by Personal Trainer
     *
     * @return ClientAssessment[]
     */
    public function findByPersonalTrainer(PersonalTrainer $pt): array;

    /**
     * Find assessments by client
     *
     * @return ClientAssessment[]
     */
    public function findByClient(User $client): array;

    /**
     * Find latest completed assessment for a client
     */
    public function findLatestByClient(User $client): ?ClientAssessment;
}
