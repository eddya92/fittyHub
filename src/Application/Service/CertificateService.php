<?php

namespace App\Application\Service;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepository;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\FileUploadService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CertificateService
{
    public function __construct(
        private MedicalCertificateRepository $certificateRepository,
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Approva un certificato medico
     */
    public function approveCertificate(MedicalCertificate $certificate): void
    {
        if ($certificate->getStatus() !== 'pending_review') {
            throw new \RuntimeException('Puoi approvare solo certificati in attesa di revisione.');
        }

        $certificate->setStatus('approved');
        $certificate->setReviewedAt(new \DateTimeImmutable());
        $this->certificateRepository->save($certificate, true);
    }

    /**
     * Rifiuta un certificato medico
     */
    public function rejectCertificate(MedicalCertificate $certificate): void
    {
        if ($certificate->getStatus() !== 'pending_review') {
            throw new \RuntimeException('Puoi rifiutare solo certificati in attesa di revisione.');
        }

        $certificate->setStatus('rejected');
        $certificate->setReviewedAt(new \DateTimeImmutable());
        $this->certificateRepository->save($certificate, true);
    }

    /**
     * Carica e approva un certificato medico
     */
    public function uploadCertificate(
        User $user,
        UploadedFile $file,
        string $certificateType,
        string $expiryDate,
        ?string $notes = null
    ): MedicalCertificate {
        // Upload file
        $filePath = $this->fileUploadService->upload($file, 'medical_certificates');

        // Crea certificato
        $certificate = new MedicalCertificate();
        $certificate->setUser($user);
        $certificate->setCertificateType($certificateType);
        $certificate->setExpiryDate(new \DateTime($expiryDate));
        $certificate->setFilePath($filePath);
        $certificate->setStatus('approved'); // Admin lo carica già approvato
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setReviewedAt(new \DateTimeImmutable());

        if ($notes) {
            $certificate->setNotes($notes);
        }

        $this->certificateRepository->save($certificate, true);

        return $certificate;
    }

    /**
     * Ottiene statistiche certificati
     */
    public function getStats(): array
    {
        return [
            'pending' => $this->certificateRepository->count(['status' => 'pending_review']),
            'approved' => $this->certificateRepository->count(['status' => 'approved']),
            'rejected' => $this->certificateRepository->count(['status' => 'rejected']),
            'expired' => $this->certificateRepository->count(['status' => 'expired']),
        ];
    }
}
