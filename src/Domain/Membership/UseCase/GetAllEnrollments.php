<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;

/**
 * Use Case: Ottiene tutte le quote iscrizione
 */
class GetAllEnrollments
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * @return array<Enrollment>
     */
    public function execute(): array
    {
        return $this->enrollmentRepository->findBy([], ['createdAt' => 'DESC']);
    }
}
