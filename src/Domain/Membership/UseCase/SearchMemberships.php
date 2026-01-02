<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Repository\MembershipRepositoryInterface;

/**
 * Use Case: Cerca abbonamenti con paginazione
 *
 * Cosa fa: cerca utenti unici con il loro abbonamento più recente
 * Supporta filtri per status, ricerca testuale, palestra e paginazione
 */
class SearchMemberships
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository
    ) {}

    /**
     * @return array{memberships: array, total_users: int, total_pages: int, current_page: int}
     */
    public function execute(
        ?string $status = null,
        ?string $search = null,
        ?int $gymId = null,
        int $page = 1,
        int $perPage = 50
    ): array {
        $offset = ($page - 1) * $perPage;

        $memberships = $this->membershipRepository->findUniqueUsersWithLatestMembership(
            $status,
            $search,
            $gymId,
            $perPage,
            $offset
        );

        $totalUsers = $status
            ? $this->membershipRepository->countUniqueUsers($status)
            : $this->membershipRepository->countUniqueUsers();

        $totalPages = ceil($totalUsers / $perPage);

        return [
            'memberships' => $memberships,
            'total_users' => $totalUsers,
            'total_pages' => $totalPages,
            'current_page' => $page,
        ];
    }
}
