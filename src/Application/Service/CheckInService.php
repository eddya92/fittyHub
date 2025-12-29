<?php

namespace App\Application\Service;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Repository\GymAttendanceRepository;
use App\Domain\Membership\Repository\GymMembershipRepository;
use App\Domain\Medical\Repository\MedicalCertificateRepository;
use App\Domain\User\Entity\User;

class CheckInService
{
    public function __construct(
        private GymAttendanceRepository $attendanceRepository,
        private GymMembershipRepository $membershipRepository,
        private MedicalCertificateRepository $certificateRepository
    ) {}

    /**
     * Verifica se l'utente può fare check-in in palestra
     *
     * @return array ['allowed' => bool, 'reason' => string|null]
     */
    public function canCheckIn(User $user, Gym $gym): array
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
        $certificate = $this->certificateRepository->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.gym = :gym')
            ->andWhere('c.expiryDate >= :today')
            ->setParameter('user', $user)
            ->setParameter('gym', $gym)
            ->setParameter('today', $now)
            ->orderBy('c.expiryDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

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
     * Effettua il check-out per un utente
     */
    public function checkOut(GymAttendance $attendance): void
    {
        if ($attendance->getCheckOutTime()) {
            throw new \RuntimeException('Check-out già effettuato.');
        }

        $attendance->setCheckOutTime(new \DateTime());
        $this->attendanceRepository->save($attendance, true);
    }

    /**
     * Trova l'ultimo check-in attivo (senza check-out) per un utente
     */
    public function findActiveCheckIn(User $user, Gym $gym): ?GymAttendance
    {
        return $this->attendanceRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.gym = :gym')
            ->andWhere('a.checkOutTime IS NULL')
            ->setParameter('user', $user)
            ->setParameter('gym', $gym)
            ->orderBy('a.checkInTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Ottiene lo storico presenze di un utente
     */
    public function getUserAttendanceHistory(User $user, Gym $gym, int $limit = 10): array
    {
        return $this->attendanceRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.gym = :gym')
            ->setParameter('user', $user)
            ->setParameter('gym', $gym)
            ->orderBy('a.checkInTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Ottiene le statistiche presenze per la dashboard
     */
    public function getAttendanceStats(Gym $gym, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $qb = $this->attendanceRepository->createQueryBuilder('a')
            ->where('a.gym = :gym')
            ->setParameter('gym', $gym);

        if ($from) {
            $qb->andWhere('a.checkInTime >= :from')
               ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('a.checkInTime <= :to')
               ->setParameter('to', $to);
        }

        $totalCheckIns = (clone $qb)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        $avgDuration = (clone $qb)
            ->select('AVG(a.duration)')
            ->andWhere('a.duration IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $uniqueUsers = (clone $qb)
            ->select('COUNT(DISTINCT a.user)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_check_ins' => (int)$totalCheckIns,
            'average_duration_minutes' => $avgDuration ? round($avgDuration) : null,
            'unique_users' => (int)$uniqueUsers
        ];
    }
}