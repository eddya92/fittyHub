<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;

/**
 * Use Case: Cancella un abbonamento
 *
 * Cosa fa: imposta lo status dell'abbonamento a "cancelled"
 * Solo abbonamenti attivi possono essere cancellati
 */
class CancelMembership
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository
    ) {}

    /**
     * @throws \RuntimeException se non è possibile cancellare
     */
    public function execute(GymMembership $membership): void
    {
        if ($membership->getStatus() !== 'active') {
            throw new \RuntimeException('Puoi cancellare solo iscrizioni attive.');
        }

        $membership->setStatus('cancelled');
        $this->membershipRepository->save($membership, true);
    }
}
