<?php

namespace App\Domain\Gym\Service;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\User\Entity\User;

class CheckInService
{
    public function __construct(
        private GymAttendanceRepositoryInterface $attendanceRepository,
        private MembershipRepositoryInterface $membershipRepository,
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * Verifica se l'utente può fare check-in in palestra
     *
     * @return array ['allowed' => bool, 'reason' => string|null]
     */
    public function canCheckIn(User $user, Gym $gym): array
    {
        // 1. Verifica abbonamento attivo
        $membership = $this->membershipRepository->findActiveByGym($gym, $user);

        if (!$membership) {
            return [
                'allowed' => false,
                'reason' => 'Nessun abbonamento attivo trovato.'
            ];
        }

        // 2. Verifica se l'abbonamento è scaduto
        $now = new \DateTime();
        if ($membership->getEndDate() < $now) {
            return [
                'allowed' => false,
                'reason' => 'Abbonamento scaduto il ' . $membership->getEndDate()->format('d/m/Y') . '.'
            ];
        }

        // 3. Verifica certificato medico
        $certificate = $this->certificateRepository->findValidCertificateForUserAndGym($user, $gym);

        if (!$certificate) {
            return [
                'allowed' => false,
                'reason' => 'Certificato medico mancante o scaduto.'
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'membership' => $membership
        ];
    }

    /**
     * Effettua il check-in per un utente
     */
    public function checkIn(User $user, Gym $gym, string $type = 'gym_entrance'): GymAttendance
    {
        $validation = $this->canCheckIn($user, $gym);

        if (!$validation['allowed']) {
            throw new \RuntimeException($validation['reason']);
        }

        $attendance = new GymAttendance();
        $attendance->setUser($user);
        $attendance->setGym($gym);
        $attendance->setGymMembership($validation['membership']);
        $attendance->setType($type);
        $attendance->setCheckInTime(new \DateTime());

        $this->attendanceRepository->save($attendance, true);

        return $attendance;
    }

    /**
     * Ottiene lo storico presenze di un utente
     */
    public function getUserAttendanceHistory(User $user, Gym $gym, int $limit = 10): array
    {
        return $this->attendanceRepository->findByUserAndGym($user, $gym, $limit);
    }

    /**
     * Ottiene le statistiche presenze per la dashboard
     */
    public function getAttendanceStats(Gym $gym, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $totalCheckIns = $this->attendanceRepository->countByGymAndDateRange($gym, $from, $to);
        $uniqueUsers = $this->attendanceRepository->countUniqueUsersByGymAndDateRange($gym, $from, $to);

        return [
            'total_check_ins' => $totalCheckIns,
            'unique_users' => $uniqueUsers
        ];
    }
}