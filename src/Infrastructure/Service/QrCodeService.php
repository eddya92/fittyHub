<?php

namespace App\Infrastructure\Service;

use App\Domain\Gym\Entity\Gym;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Servizio per generare QR code
 */
class QrCodeService
{
    public function __construct(
        private string $appBaseUrl = 'https://fittyhub.app'
    ) {}

    /**
     * Genera URL deep link per la palestra
     * L'app mobile intercetterà questo URL
     */
    public function generateDeepLink(Gym $gym): string
    {
        return $this->appBaseUrl . '/join/' . $gym->getSlug();
    }

    /**
     * Genera HTML per visualizzare QR code
     */
    public function generateQrCodeHtml(Gym $gym): string
    {
        $deepLink = $this->generateDeepLink($gym);

        // Genera QR code usando endroid/qr-code v6
        $qrCode = new QrCode(
            data: $deepLink,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Converti in base64 per embedding diretto nell'HTML
        $dataUri = $result->getDataUri();

        return sprintf(
            '<div class="qr-code-container text-center">
                <img src="%s" alt="QR Code Palestra" class="mx-auto" style="width: 300px; height: 300px;">
            </div>',
            htmlspecialchars($dataUri)
        );
    }

    /**
     * Genera codice numerico semplice (alternativa al QR)
     * Utente può digitare questo codice nell'app
     */
    public function generateSimpleCode(Gym $gym): string
    {
        // Usa gli ultimi 6 caratteri dello slug o l'ID
        return strtoupper(substr($gym->getSlug(), -6));
    }
}
