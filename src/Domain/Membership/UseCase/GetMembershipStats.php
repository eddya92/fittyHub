<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Repository\MembershipRepositoryInterface;

/**
 * Use Case: Ottiene statistiche sugli abbonamenti
 * Conta utenti unici per stato (attivi, scaduti, cancellati)
 */
class GetMembershipStats
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository
    ) {}

    /**
     * @return array{total: int, active: int, expired: int, cancelled: int}
     */
    public function execute(): array
    {
        return [
            'total' => $this->membershipRepository->countUniqueUsers(),
            'active' => $this->membershipRepository->countUniqueUsers('active'),
            'expired' => $this->membershipRepository->countUniqueUsers('expired'),
            'cancelled' => $this->membershipRepository->countUniqueUsers('cancelled'),
        ];
    }
}
