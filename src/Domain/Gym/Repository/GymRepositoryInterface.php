<?php

namespace App\Domain\Gym\Repository;

use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;

/**
 * Repository interface per Gym
 *
 * Nota: Metodi standard (find, findAll, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface GymRepositoryInterface
{
    /**
     * Trova solo palestre attive
     */
    public function findActiveGyms(): array;

    /**
     * Trova palestre gestite da un admin specifico
     */
    public function findByAdmin(User $admin): array;

    /**
     * Trova palestre per città
     */
    public function findByCity(string $city): array;
}
