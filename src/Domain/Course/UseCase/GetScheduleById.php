<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;

/**
 * Use Case: Ottiene uno schedule per ID
 */
class GetScheduleById
{
    public function __construct(
        private CourseScheduleRepositoryInterface $scheduleRepository
    ) {}

    /**
     * @throws \RuntimeException se lo schedule non esiste
     */
    public function execute(int $id): CourseSchedule
    {
        $schedule = $this->scheduleRepository->find($id);

        if (!$schedule) {
            throw new \RuntimeException('Orario non trovato.');
        }

        return $schedule;
    }
}
