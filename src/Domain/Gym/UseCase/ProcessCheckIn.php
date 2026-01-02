<?php

namespace App\Domain\Gym\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\User\Entity\User;

/**
 * Use Case: Effettua il check-in per un utente
 */
class ProcessCheckIn
{
    public function __construct(
        private ValidateCheckIn $validateCheckIn,
        private GymAttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * @throws \RuntimeException se il check-in non è consentito
     */
    public function execute(User $user, Gym $gym, string $type = 'gym_entrance'): GymAttendance
    {
        $validation = $this->validateCheckIn->execute($user, $gym);

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
}
