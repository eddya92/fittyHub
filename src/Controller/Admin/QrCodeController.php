<?php

namespace App\Controller\Admin;

use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Infrastructure\Service\QrCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller per QR Code palestra
 */
#[Route('/admin/qr-code')]
class QrCodeController extends AbstractController
{
    public function __construct(
        private GymRepositoryInterface $gymRepository,
        private QrCodeService $qrCodeService
    ) {}

    /**
     * Visualizza QR code della palestra
     */
    #[Route('', name: 'admin_qr_code')]
    public function show(): Response
    {
        // TODO: filtrare per palestra dell'admin loggato
        // Per ora prende la prima palestra
        $gym = $this->gymRepository->findOneBy(['isActive' => true]);

        if (!$gym) {
            $this->addFlash('error', 'Nessuna palestra trovata.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $deepLink = $this->qrCodeService->generateDeepLink($gym);
        $simpleCode = $this->qrCodeService->generateSimpleCode($gym);
        $qrHtml = $this->qrCodeService->generateQrCodeHtml($gym);

        return $this->render('admin/qr_code/show.html.twig', [
            'gym' => $gym,
            'deep_link' => $deepLink,
            'simple_code' => $simpleCode,
            'qr_html' => $qrHtml,
            'slug' => $gym->getSlug(),
        ]);
    }
}
