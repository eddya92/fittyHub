<?php

namespace App\Domain\Gym\Repository;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\User\Entity\User;

/**
 * Repository interface per GymAttendance
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface GymAttendanceRepositoryInterface
{
    /**
     * Ottiene le presenze recenti per una palestra
     */
    public function findRecentByGym(Gym $gym, string $type = 'gym_entrance', int $limit = 20): array;

    /**
     * Ottiene lo storico presenze di un utente
     */
    public function findByUserAndGym(User $user, Gym $gym, int $limit = 50): array;

    /**
     * Conta le presenze per una palestra in un periodo
     */
    public function countByGymAndDateRange(Gym $gym, ?\DateTime $from = null, ?\DateTime $to = null): int;

    /**
     * Conta gli utenti unici per una palestra in un periodo
     */
    public function countUniqueUsersByGymAndDateRange(Gym $gym, ?\DateTime $from = null, ?\DateTime $to = null): int;

    /**
     * Salva una presenza
     */
    public function save(GymAttendance $attendance, bool $flush = false): void;
}
