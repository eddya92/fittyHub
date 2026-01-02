<?php

namespace App\Domain\Medical\UseCase;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;

/**
 * Use Case: Ottiene un certificato medico per ID
 */
class GetCertificateById
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * @throws \RuntimeException se il certificato non esiste
     */
    public function execute(int $id): MedicalCertificate
    {
        $certificate = $this->certificateRepository->find($id);

        if (!$certificate) {
            throw new \RuntimeException('Certificato medico non trovato.');
        }

        return $certificate;
    }
}
