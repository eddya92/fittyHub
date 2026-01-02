<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;
use App\Domain\User\Entity\User;

/**
 * Use Case: Ottiene lo storico quote iscrizione di un utente
 */
class GetUserEnrollmentHistory
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * @return array<Enrollment>
     */
    public function execute(User $user): array
    {
        return $this->enrollmentRepository->findByUser($user);
    }
}
