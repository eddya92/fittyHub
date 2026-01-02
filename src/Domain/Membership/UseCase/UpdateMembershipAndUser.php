<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;

/**
 * Use Case: Aggiorna i dati di un'iscrizione e dell'utente associato
 */
class UpdateMembershipAndUser
{
    public function __construct(
        private MembershipRepositoryInterface $membershipRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Aggiorna abbonamento e dati utente
     *
     * @param array<string, mixed> $data Dati da aggiornare
     */
    public function execute(GymMembership $membership, array $data): void
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

        $membership->setUpdatedAt(new \DateTimeImmutable());

        $this->userRepository->save($user, false);
        $this->membershipRepository->save($membership, true);
    }

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
}
