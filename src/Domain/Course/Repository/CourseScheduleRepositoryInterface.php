<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseSchedule;

/**
 * Repository interface per CourseSchedule
 */
interface CourseScheduleRepositoryInterface
{
    public function save(CourseSchedule $schedule, bool $flush = false): void;

    public function remove(CourseSchedule $schedule, bool $flush = false): void;

    public function findAll(): array;
}
