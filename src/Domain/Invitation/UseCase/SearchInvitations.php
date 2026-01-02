<?php

namespace App\Domain\Invitation\UseCase;

use App\Domain\Invitation\Repository\InvitationRepositoryInterface;

/**
 * Use Case: Cerca inviti con filtri
 */
class SearchInvitations
{
    public function __construct(
        private InvitationRepositoryInterface $invitationRepository
    ) {}

    /**
     * @return array<GymPTInvitation>
     */
    public function execute(?string $status = null, ?string $search = null): array
    {
        return $this->invitationRepository->findWithFilters($status, $search);
    }
}
