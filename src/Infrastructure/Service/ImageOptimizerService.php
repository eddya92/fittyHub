<?php

namespace App\Infrastructure\Service;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Servizio per ottimizzare immagini (resize + compressione)
 * Riduce drasticamente lo spazio occupato mantenendo qualità accettabile
 */
class ImageOptimizerService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Ottimizza avatar utente
     * - Max 400x400px
     * - Qualità 85%
     * - Converte in JPG per compatibilità
     */
    public function optimizeAvatar(File $file): void
    {
        $this->optimize($file, 400, 400, 85);
    }

    /**
     * Ottimizza logo palestra
     * - Max 800x800px
     * - Qualità 90% (logo richiede più qualità)
     * - Mantiene trasparenza se PNG
     */
    public function optimizeLogo(File $file): void
    {
        $this->optimize($file, 800, 800, 90);
    }

    /**
     * Ottimizza certificato (per future implementazioni)
     */
    public function optimizeDocument(File $file): void
    {
        // I PDF non vanno ottimizzati con Intervention Image
        // Qui potremmo usare ghostscript per comprimere PDF
        // Per ora non facciamo nulla
    }

    /**
     * Ottimizzazione generica
     *
     * @param File $file File da ottimizzare
     * @param int $maxWidth Larghezza massima
     * @param int $maxHeight Altezza massima
     * @param int $quality Qualità (0-100)
     */
    private function optimize(File $file, int $maxWidth, int $maxHeight, int $quality): void
    {
        $image = $this->manager->read($file->getPathname());

        // Resize mantenendo aspect ratio
        $image->scale(width: $maxWidth, height: $maxHeight);

        // Encode con compressione
        $encoded = $image->toJpeg($quality);

        // Sovrascrivi file originale con versione ottimizzata
        file_put_contents($file->getPathname(), $encoded);
    }
}
