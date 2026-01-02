<?php

namespace App\Domain\Medical\UseCase;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;

/**
 * Use Case: Rifiuta un certificato medico
 */
class RejectCertificate
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * @throws \RuntimeException se il certificato non può essere rifiutato
     */
    public function execute(MedicalCertificate $certificate): void
    {
        if ($certificate->getStatus() !== 'pending_review') {
            throw new \RuntimeException('Puoi rifiutare solo certificati in attesa di revisione.');
        }

        $certificate->setStatus('rejected');
        $certificate->setReviewedAt(new \DateTimeImmutable());

        $this->certificateRepository->save($certificate, true);
    }
}
