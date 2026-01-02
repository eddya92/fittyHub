<?php

namespace App\Domain\Gym\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;

/**
 * Use Case: Ottiene statistiche presenze per una palestra
 */
class GetAttendanceStats
{
    public function __construct(
        private GymAttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * @return array{total_check_ins: int, unique_users: int}
     */
    public function execute(Gym $gym, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        return [
            'total_check_ins' => $this->attendanceRepository->countByGymAndDateRange($gym, $from, $to),
            'unique_users' => $this->attendanceRepository->countUniqueUsersByGymAndDateRange($gym, $from, $to)
        ];
    }
}
