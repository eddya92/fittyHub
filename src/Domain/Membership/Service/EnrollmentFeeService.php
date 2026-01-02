<?php

namespace App\Domain\Membership\Service;

use App\Domain\Membership\Entity\Enrollment;
use App\Domain\Membership\Repository\EnrollmentRepository;
use App\Domain\User\Entity\User;
use App\Domain\Gym\Entity\Gym;

/**
 * Service per la gestione delle quote iscrizione
 */
class EnrollmentFeeService
{
    public function __construct(
        private EnrollmentRepository $enrollmentRepository
    ) {}

    /**
     * Crea una nuova quota iscrizione
     */
    public function createEnrollment(User $user, Gym $gym, array $data): Enrollment
    {
        $enrollment = new Enrollment();
        $enrollment->setUser($user);
        $enrollment->setGym($gym);
        $enrollment->setAmount($data['amount']);
        $enrollment->setPaymentDate(new \DateTime($data['payment_date']));

        if (!empty($data['expiry_date'])) {
            $enrollment->setExpiryDate(new \DateTime($data['expiry_date']));
        }

        $enrollment->setNotes($data['notes'] ?? null);
        $enrollment->setStatus('active');

        $this->enrollmentRepository->save($enrollment, true);

        return $enrollment;
    }

    /**
     * Scade una quota iscrizione
     */
    public function expireEnrollment(Enrollment $enrollment): void
    {
        $enrollment->setStatus('expired');
        $enrollment->setUpdatedAt(new \DateTimeImmutable());

        $this->enrollmentRepository->save($enrollment, true);
    }
}
