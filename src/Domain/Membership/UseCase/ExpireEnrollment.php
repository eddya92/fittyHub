<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\Enrollment;
use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;

/**
 * Use Case: Scade una quota iscrizione
 */
class ExpireEnrollment
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * @throws \RuntimeException se la quota non può essere scaduta
     */
    public function execute(Enrollment $enrollment): void
    {
        if ($enrollment->getStatus() !== 'active') {
            throw new \RuntimeException('Puoi scadere solo quote attive.');
        }

        $enrollment->setStatus('expired');

        $this->enrollmentRepository->save($enrollment, true);
    }
}
