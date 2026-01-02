<?php

namespace App\Domain\Medical\UseCase;

use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;

/**
 * Use Case: Cerca certificati medici con filtri
 */
class SearchCertificates
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * @return array<MedicalCertificate>
     */
    public function execute(?string $status = null, ?string $search = null): array
    {
        return $this->certificateRepository->findWithFilters($status, $search);
    }
}
