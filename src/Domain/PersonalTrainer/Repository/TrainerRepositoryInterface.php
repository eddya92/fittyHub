<?php

namespace App\Domain\PersonalTrainer\Repository;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;

/**
 * Repository interface per PersonalTrainer
 *
 * Nota: Metodi standard (find, findBy, findAll, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface TrainerRepositoryInterface
{
    /**
     * Trova trainer con filtri custom
     */
    public function findWithFilters(?string $search, ?string $specialization): array;
}
