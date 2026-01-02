<?php

namespace App\Domain\Medical\UseCase;

use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;

/**
 * Use Case: Ottiene statistiche sui certificati medici
 */
class GetCertificateStats
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * @return array{pending: int, approved: int, rejected: int, expired: int}
     */
    public function execute(): array
    {
        return [
            'pending' => $this->certificateRepository->count(['status' => 'pending_review']),
            'approved' => $this->certificateRepository->count(['status' => 'approved']),
            'rejected' => $this->certificateRepository->count(['status' => 'rejected']),
            'expired' => $this->certificateRepository->count(['status' => 'expired']),
        ];
    }
}
