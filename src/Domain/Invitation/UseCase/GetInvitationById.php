<?php

namespace App\Domain\Invitation\UseCase;

use App\Domain\Invitation\Entity\GymPTInvitation;
use App\Domain\Invitation\Repository\InvitationRepositoryInterface;

/**
 * Use Case: Ottiene un invito per ID
 */
class GetInvitationById
{
    public function __construct(
        private InvitationRepositoryInterface $invitationRepository
    ) {}

    /**
     * @throws \RuntimeException se l'invito non esiste
     */
    public function execute(int $id): GymPTInvitation
    {
        $invitation = $this->invitationRepository->find($id);

        if (!$invitation) {
            throw new \RuntimeException('Invito non trovato.');
        }

        return $invitation;
    }
}
