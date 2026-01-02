<?php

namespace App\Domain\Membership\Repository;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;

/**
 * Repository interface per GymMembership
 * Il Domain definisce COSA serve, Infrastructure implementa COME
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface MembershipRepositoryInterface
{
    /**
     * Trova abbonamenti attivi per un utente
     */
    public function findActiveByUser(User $user): array;

    /**
     * Trova abbonamento attivo per utente e palestra
     */
    public function findActiveByGym(Gym $gym, User $user): ?GymMembership;

    /**
     * Trova utenti unici con il loro ultimo abbonamento
     */
    public function findUniqueUsersWithLatestMembership(
        ?string $status,
        ?string $search,
        ?int $gymId,
        int $limit,
        int $offset
    ): array;

    /**
     * Conta utenti unici (business logic custom)
     */
    public function countUniqueUsers(?string $status = null): int;
}
