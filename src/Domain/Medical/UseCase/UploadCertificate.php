<?php

namespace App\Domain\Medical\UseCase;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\FileUploadService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Use Case: Carica un certificato medico (già approvato da admin)
 */
class UploadCertificate
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository,
        private FileUploadService $fileUploadService
    ) {}

    /**
     * @throws \Exception se l'upload fallisce
     */
    public function execute(
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
}
