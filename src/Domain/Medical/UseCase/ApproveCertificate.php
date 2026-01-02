<?php

namespace App\Domain\Medical\UseCase;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;

/**
 * Use Case: Approva un certificato medico
 */
class ApproveCertificate
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * @throws \RuntimeException se il certificato non può essere approvato
     */
    public function execute(MedicalCertificate $certificate): void
    {
        if ($certificate->getStatus() !== 'pending_review') {
            throw new \RuntimeException('Puoi approvare solo certificati in attesa di revisione.');
        }

        $certificate->setStatus('approved');
        $certificate->setReviewedAt(new \DateTimeImmutable());

        $this->certificateRepository->save($certificate, true);
    }
}
