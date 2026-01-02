<?php

namespace App\Domain\Gym\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;

/**
 * Use Case: Ottiene le presenze recenti per una palestra
 */
class GetRecentAttendances
{
    public function __construct(
        private GymAttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * @return array<GymAttendance>
     */
    public function execute(Gym $gym, int $limit = 20): array
    {
        return $this->attendanceRepository->findRecentByGym($gym, 'gym_entrance', $limit);
    }
}
