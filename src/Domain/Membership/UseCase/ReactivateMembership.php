<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;

/**
 * Use Case: Riattiva un'iscrizione cancellata o scaduta
 */
class ReactivateMembership
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository
    ) {}

    /**
     * @throws \RuntimeException se l'iscrizione non può essere riattivata
     */
    public function execute(GymMembership $membership): void
    {
        if ($membership->getStatus() !== 'cancelled' && $membership->getStatus() !== 'expired') {
            throw new \RuntimeException('Puoi riattivare solo iscrizioni cancellate o scadute.');
        }

        $membership->setStatus('active');
        $membership->setUpdatedAt(new \DateTimeImmutable());

        $this->membershipRepository->save($membership, true);
    }
}
