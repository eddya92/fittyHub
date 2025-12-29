<?php

namespace App\Domain\Medical\Service;

use App\Domain\Medical\Entity\MedicalCertificate;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class MedicalCertificateUploadService
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $uploadDirectory
    ) {
    }

    public function upload(UploadedFile $file, MedicalCertificate $certificate): string
    {
        // Valida il file
        $this->validateFile($file);

        // Genera nome file sicuro
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Sposta il file nella directory di upload
        try {
            $file->move($this->uploadDirectory, $fileName);
        } catch (FileException $e) {
            throw new \RuntimeException('Errore durante il caricamento del file: ' . $e->getMessage());
        }

        return $fileName;
    }

    private function validateFile(UploadedFile $file): void
    {
        // Valida dimensione file (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \RuntimeException('Il file è troppo grande. Dimensione massima: 5MB');
        }

        // Valida tipo MIME
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \RuntimeException('Tipo di file non valido. Formati accettati: PDF, JPEG, PNG');
        }
    }

    public function delete(string $fileName): void
    {
        $filePath = $this->uploadDirectory . '/' . $fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getFilePath(string $fileName): string
    {
        return $this->uploadDirectory . '/' . $fileName;
    }
}
