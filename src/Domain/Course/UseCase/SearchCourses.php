<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Repository\CourseRepositoryInterface;

/**
 * Use Case: Cerca corsi con filtri
 */
class SearchCourses
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository
    ) {}

    public function execute(?string $search, ?string $category, ?string $status): array
    {
        return $this->courseRepository->findWithFilters($search, $category, $status);
    }
}
