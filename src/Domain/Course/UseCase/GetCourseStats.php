<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Repository\CourseRepositoryInterface;

/**
 * Use Case: Ottiene statistiche corsi
 */
class GetCourseStats
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository
    ) {}

    /**
     * @return array{total: int, active: int, suspended: int}
     */
    public function execute(): array
    {
        return [
            'total' => $this->courseRepository->count([]),
            'active' => $this->courseRepository->countByStatus('active'),
            'suspended' => $this->courseRepository->countByStatus('suspended'),
        ];
    }
}
