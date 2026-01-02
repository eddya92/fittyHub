<?php

namespace App\Domain\Invitation\UseCase;

use App\Domain\Invitation\Repository\InvitationRepositoryInterface;

/**
 * Use Case: Ottiene statistiche sugli inviti
 */
class GetInvitationStats
{
    public function __construct(
        private InvitationRepositoryInterface $invitationRepository
    ) {}

    /**
     * @return array{pending: int, accepted: int, declined: int, expired: int}
     */
    public function execute(): array
    {
        return [
            'pending' => $this->invitationRepository->count(['status' => 'pending']),
            'accepted' => $this->invitationRepository->count(['status' => 'accepted']),
            'declined' => $this->invitationRepository->count(['status' => 'declined']),
            'expired' => $this->invitationRepository->count(['status' => 'expired']),
        ];
    }
}
