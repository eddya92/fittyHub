<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\Enrollment;
use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;
use App\Domain\User\Entity\User;

/**
 * Use Case: Crea una nuova quota iscrizione
 */
class CreateEnrollment
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * @param array<string, mixed> $data Dati quota
     * @throws \RuntimeException se i dati non sono validi
     */
    public function execute(User $user, Gym $gym, array $data): Enrollment
    {
        $enrollment = new Enrollment();
        $enrollment->setUser($user);
        $enrollment->setGym($gym);
        $enrollment->setAmount($data['amount'] ?? 0);
        $enrollment->setPaymentDate(new \DateTime($data['payment_date'] ?? 'now'));
        $enrollment->setExpiryDate(new \DateTime($data['expiry_date'] ?? '+1 year'));
        $enrollment->setStatus('active');

        if (!empty($data['notes'])) {
            $enrollment->setNotes($data['notes']);
        }

        $this->enrollmentRepository->save($enrollment, true);

        return $enrollment;
    }
}
