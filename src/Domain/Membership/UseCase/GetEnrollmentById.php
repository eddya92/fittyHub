<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\Enrollment;
use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;

/**
 * Use Case: Ottiene una quota iscrizione per ID
 */
class GetEnrollmentById
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * @throws \RuntimeException se la quota non esiste
     */
    public function execute(int $id): Enrollment
    {
        $enrollment = $this->enrollmentRepository->find($id);

        if (!$enrollment) {
            throw new \RuntimeException('Quota iscrizione non trovata.');
        }

        return $enrollment;
    }
}
