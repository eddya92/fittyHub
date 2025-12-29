<?php

namespace App\Infrastructure\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    public function __construct(
        private string $uploadDirectory,
        private SluggerInterface $slugger
    ) {
    }

    public function upload(UploadedFile $file, string $subdirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->uploadDirectory;
        if ($subdirectory) {
            $targetDirectory .= '/' . $subdirectory;
        }

        // Crea la directory se non esiste
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new \Exception('Errore durante il caricamento del file: ' . $e->getMessage());
        }

        return $subdirectory ? $subdirectory . '/' . $fileName : $fileName;
    }

    public function delete(string $filePath): void
    {
        $fullPath = $this->uploadDirectory . '/' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
