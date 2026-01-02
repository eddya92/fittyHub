<?php

namespace App\Domain\Gym\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\User\Entity\User;

/**
 * Use Case: Verifica se un utente può fare check-in
 */
class ValidateCheckIn
{
    public function __construct(
        private GymAttendanceRepositoryInterface $attendanceRepository,
        private MembershipRepositoryInterface $membershipRepository,
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * @return array{allowed: bool, reason: string|null, membership: GymMembership|null}
     */
    public function execute(User $user, Gym $gym): array
    {
        // 1. Verifica abbonamento attivo
        $membership = $this->membershipRepository->findOneBy([
            'user' => $user,
            'gym' => $gym,
            'status' => 'active'
        ]);

        if (!$membership) {
            return [
                'allowed' => false,
                'reason' => 'Nessun abbonamento attivo trovato.',
                'membership' => null
            ];
        }

        // 2. Verifica se l'abbonamento è scaduto
        $now = new \DateTime();
        if ($membership->getEndDate() < $now) {
            return [
                'allowed' => false,
                'reason' => 'Abbonamento scaduto il ' . $membership->getEndDate()->format('d/m/Y') . '.',
                'membership' => null
            ];
        }

        // 3. Verifica certificato medico
        $certificate = $this->certificateRepository->findBy([
            'user' => $user,
            'status' => 'approved'
        ]);

        $validCertificate = null;
        foreach ($certificate as $cert) {
            if ($cert->getExpiryDate() >= $now) {
                $validCertificate = $cert;
                break;
            }
        }

        if (!$validCertificate) {
            return [
                'allowed' => false,
                'reason' => 'Certificato medico mancante o scaduto.',
                'membership' => null
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'membership' => $membership
        ];
    }
}
