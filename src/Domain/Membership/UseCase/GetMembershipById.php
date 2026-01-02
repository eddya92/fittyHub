<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;

/**
 * Use Case: Ottiene un abbonamento per ID
 *
 * Cosa fa: cerca un abbonamento, se non esiste lancia eccezione
 */
class GetMembershipById
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository
    ) {}

    /**
     * @throws \RuntimeException se l'abbonamento non esiste
     */
    public function execute(int $id): GymMembership
    {
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            throw new \RuntimeException('Iscrizione non trovata.');
        }

        return $membership;
    }
}
