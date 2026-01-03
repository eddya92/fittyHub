<?php

namespace App\Domain\Gym\Repository;

use App\Domain\Gym\Entity\GymSettings;

/**
 * Repository interface per GymSettings
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface GymSettingsRepositoryInterface
{
    /**
     * Save GymSettings entity
     */
    public function save(GymSettings $entity, bool $flush = false): void;

    /**
     * Remove GymSettings entity
     */
    public function remove(GymSettings $entity, bool $flush = false): void;
}
