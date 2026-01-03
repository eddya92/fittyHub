<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\GymCourse;
use App\Domain\Gym\Entity\Gym;

/**
 * Repository interface per GymCourse
 */
interface CourseRepositoryInterface
{
    public function save(GymCourse $course, bool $flush = false): void;

    public function remove(GymCourse $course, bool $flush = false): void;

    /**
     * Trova corsi con filtri custom
     */
    public function findWithFilters(?string $search, ?string $category, ?string $status): array;

    /**
     * Conta corsi per status (business logic custom)
     */
    public function countByStatus(string $status): int;

    /**
     * Trova corsi attivi con schedules caricati (eager loading)
     */
    public function findActiveWithSchedules(): array;
}
