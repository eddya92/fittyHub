<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;

/**
 * Use Case: Ottiene un'iscrizione al corso per ID
 */
class GetEnrollmentById
{
    public function __construct(
        private CourseEnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * @throws \RuntimeException se l'iscrizione non esiste
     */
    public function execute(int $id): CourseEnrollment
    {
        $enrollment = $this->enrollmentRepository->find($id);

        if (!$enrollment) {
            throw new \RuntimeException('Iscrizione non trovata.');
        }

        return $enrollment;
    }
}
