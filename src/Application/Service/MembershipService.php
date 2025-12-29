<?php

namespace App\Application\Service;

use App\Domain\Membership\Entity\GymMembership;
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
     * Ottiene statistiche memberships
     */
    public function getStats(): array
    {
        return [
            'total' => $this->membershipRepository->count([]),
            'active' => $this->membershipRepository->count(['status' => 'active']),
            'expired' => $this->membershipRepository->count(['status' => 'expired']),
            'cancelled' => $this->membershipRepository->count(['status' => 'cancelled']),
        ];
    }
}
