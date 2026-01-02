<?php

namespace App\Domain\Gym\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\User\Entity\User;

/**
 * Use Case: Ottiene lo storico presenze di un utente
 */
class GetUserAttendanceHistory
{
    public function __construct(
        private GymAttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * @return array<GymAttendance>
     */
    public function execute(User $user, Gym $gym, int $limit = 50): array
    {
        return $this->attendanceRepository->findByUserAndGym($user, $gym, $limit);
    }
}
