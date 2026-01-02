<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Repository\MembershipRepositoryInterface;

/**
 * Use Case: Ottiene abbonamenti in scadenza
 * Cerca abbonamenti attivi che scadono entro i prossimi N giorni
 */
class GetExpiringMemberships
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository
    ) {}

    /**
     * @param int $daysAhead Numero di giorni per considerare "in scadenza" (default 30)
     * @return array<GymMembership>
     */
    public function execute(int $daysAhead = 30): array
    {
        $now = new \DateTime();
        $futureDate = (clone $now)->modify("+{$daysAhead} days");

        // Trova tutti gli abbonamenti attivi
        $activeMemberships = $this->membershipRepository->findBy(['status' => 'active']);

        // Filtra quelli in scadenza
        $expiringMemberships = array_filter($activeMemberships, function ($membership) use ($now, $futureDate) {
            $endDate = $membership->getEndDate();
            return $endDate >= $now && $endDate <= $futureDate;
        });

        // Ordina per data di scadenza
        usort($expiringMemberships, function ($a, $b) {
            return $a->getEndDate() <=> $b->getEndDate();
        });

        return $expiringMemberships;
    }
}
