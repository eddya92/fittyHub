<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;

/**
 * Use Case: Rinnova un abbonamento
 *
 * Cosa fa:
 * 1. Scade l'abbonamento corrente
 * 2. Crea nuovo abbonamento con date calcolate
 * 3. Mantiene lo stesso Personal Trainer se assegnato
 */
class RenewMembership
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository
    ) {}

    public function execute(
        GymMembership $currentMembership,
        SubscriptionPlan $plan,
        ?float $actualPrice = null,
        int $bonusMonths = 0,
        ?string $discountReason = null,
        ?string $notes = null
    ): GymMembership {
        // Scade l'abbonamento corrente
        $currentMembership->setStatus('expired');
        $currentMembership->setUpdatedAt(new \DateTimeImmutable());

        // Crea nuovo abbonamento
        $newMembership = new GymMembership();
        $newMembership->setGym($currentMembership->getGym());
        $newMembership->setUser($currentMembership->getUser());
        $newMembership->setSubscriptionPlan($plan);
        $newMembership->setStatus('active');

        // Calcola date
        $startDate = new \DateTime();
        $endDate = clone $startDate;
        $totalMonths = $plan->getDuration() + $bonusMonths;
        $endDate->modify("+{$totalMonths} months");

        $newMembership->setStartDate($startDate);
        $newMembership->setEndDate($endDate);

        // Prezzi
        $newMembership->setOriginalPrice($plan->getPrice());
        $newMembership->setActualPrice($actualPrice ?? $plan->getPrice());
        $newMembership->setBonusMonths($bonusMonths);
        $newMembership->setDiscountReason($discountReason);
        $newMembership->setNotes($notes);

        // Mantieni PT
        if ($currentMembership->getAssignedPT()) {
            $newMembership->setAssignedPT($currentMembership->getAssignedPT());
        }

        // Salva tutto
        $this->membershipRepository->save($currentMembership, false);
        $this->membershipRepository->save($newMembership, true);

        return $newMembership;
    }
}
