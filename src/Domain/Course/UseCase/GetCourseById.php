<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Repository\CourseRepositoryInterface;

/**
 * Use Case: Ottiene un corso per ID
 */
class GetCourseById
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository
    ) {}

    /**
     * @throws \RuntimeException se il corso non esiste
     */
    public function execute(int $id): GymCourse
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            throw new \RuntimeException('Corso non trovato.');
        }

        return $course;
    }
}
