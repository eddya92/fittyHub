<?php

namespace App\Domain\Membership\Service;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\GymMembershipRepository;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;

class MembershipService
{
    public function __construct(
        private GymMembershipRepository $membershipRepository,
        private UserRepository $userRepository
    ) {}

    /**
     * Cancella un'iscrizione attiva
     *
     * @throws \RuntimeException se non è possibile cancellare
     */
    public function cancelMembership(GymMembership $membership): void
    {
        if ($membership->getStatus() !== 'active') {
            throw new \RuntimeException('Puoi cancellare solo iscrizioni attive.');
        }

        $membership->setStatus('cancelled');
        $this->membershipRepository->save($membership, true);
    }

    /**
     * Riattiva un'iscrizione cancellata o scaduta
     *
     * @throws \RuntimeException se non è possibile riattivare
     */
    public function reactivateMembership(GymMembership $membership): void
    {
        if ($membership->getStatus() !== 'cancelled' && $membership->getStatus() !== 'expired') {
            throw new \RuntimeException('Puoi riattivare solo iscrizioni cancellate o scadute.');
        }

        $membership->setStatus('active');
        $this->membershipRepository->save($membership, true);
    }

    /**
     * Aggiorna i dati di un'iscrizione e dell'utente associato
     */
    public function updateMembershipAndUser(GymMembership $membership, array $data): void
    {
        $user = $membership->getUser();

        // Aggiorna dati utente
        $this->updateUserData($user, $data);

        // Aggiorna dati iscrizione
        if (!empty($data['start_date'])) {
            $membership->setStartDate(new \DateTime($data['start_date']));
        }
        if (!empty($data['end_date'])) {
            $membership->setEndDate(new \DateTime($data['end_date']));
        }
        if (isset($data['notes'])) {
            $membership->setNotes($data['notes']);
        }
        if (!empty($data['status'])) {
            $membership->setStatus($data['status']);
        }

        $this->userRepository->save($user, true);
        $this->membershipRepository->save($membership, true);
    }

    /**
     * Aggiorna i dati di un utente
     */
    private function updateUserData(User $user, array $data): void
    {
        if (!empty($data['first_name'])) {
            $user->setFirstName($data['first_name']);
        }
        if (!empty($data['last_name'])) {
            $user->setLastName($data['last_name']);
        }
        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (!empty($data['phone_number'])) {
            $user->setPhoneNumber($data['phone_number']);
        }
        if (!empty($data['date_of_birth'])) {
            $user->setDateOfBirth(new \DateTime($data['date_of_birth']));
        }
        if (!empty($data['gender'])) {
            $user->setGender($data['gender']);
        }
    }

    /**
     * Rinnova un abbonamento creandone uno nuovo
     *
     * @return GymMembership il nuovo abbonamento creato
     */
    public function renewMembership(
        GymMembership $currentMembership,
        SubscriptionPlan $plan,
        array $data
    ): GymMembership {
        $bonusMonths = $data['bonus_months'] ?? 0;
        $actualPrice = $data['actual_price'] ?? $plan->getPrice();
        $discountReason = $data['discount_reason'] ?? null;
        $notes = $data['notes'] ?? null;

        // Scade l'abbonamento corrente
        $currentMembership->setStatus('expired');
        $currentMembership->setUpdatedAt(new \DateTimeImmutable());

        // Crea nuovo abbonamento
        $newMembership = new GymMembership();
        $newMembership->setGym($currentMembership->getGym());
        $newMembership->setUser($currentMembership->getUser());
        $newMembership->setSubscriptionPlan($plan);
        $newMembership->setStatus('active');

        // Calcola le date
        $startDate = new \DateTime();
        $endDate = clone $startDate;
        $totalMonths = $plan->getDuration() + $bonusMonths;
        $endDate->modify("+{$totalMonths} months");

        $newMembership->setStartDate($startDate);
        $newMembership->setEndDate($endDate);

        // Imposta i prezzi
        $newMembership->setOriginalPrice($plan->getPrice());
        $newMembership->setActualPrice($actualPrice);
        $newMembership->setBonusMonths($bonusMonths);
        $newMembership->setDiscountReason($discountReason);
        $newMembership->setNotes($notes);

        // Mantieni PT assegnato se presente
        if ($currentMembership->getAssignedPT()) {
            $newMembership->setAssignedPT($currentMembership->getAssignedPT());
        }

        $this->membershipRepository->save($currentMembership, false);
        $this->membershipRepository->save($newMembership, true);

        return $newMembership;
    }

    /**
     * Ottiene statistiche utenti unici (non conta abbonamenti duplicati)
     */
    public function getStats(): array
    {
        return [
            'total' => $this->membershipRepository->countUniqueUsers(),
            'active' => $this->membershipRepository->countUniqueUsers('active'),
            'expired' => $this->membershipRepository->countUniqueUsers('expired'),
            'cancelled' => $this->membershipRepository->countUniqueUsers('cancelled'),
        ];
    }
}
