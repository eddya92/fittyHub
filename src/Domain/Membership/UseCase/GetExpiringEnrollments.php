<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;

/**
 * Use Case: Ottiene quote iscrizione in scadenza
 */
class GetExpiringEnrollments
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * @param int $days Numero di giorni entro cui scade
     * @return array<Enrollment>
     */
    public function execute(int $days = 30): array
    {
        return $this->enrollmentRepository->findExpiringEnrollments($days);
    }
}
