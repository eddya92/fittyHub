<?php

namespace App\Controller\Api;

use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Infrastructure\Service\ImageOptimizerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/gyms')]
class GymLogoController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GymRepositoryInterface $gymRepository,
        private ImageOptimizerService $imageOptimizer
    ) {}

    /**
     * Upload logo palestra
     *
     * POST /api/gyms/{id}/logo
     * Content-Type: multipart/form-data
     * Fields:
     *   - logo (image file: jpg, jpeg, png, webp)
     */
    #[Route('/{id}/logo', name: 'api_gym_logo_upload', methods: ['POST'])]
    #[IsGranted('ROLE_GYM_ADMIN')]
    public function uploadLogo(int $id, Request $request): JsonResponse
    {
        $gym = $this->gymRepository->find($id);

        if (!$gym) {
            return $this->json(['error' => 'Palestra non trovata'], 404);
        }

        // TODO: Verificare che l'utente sia admin di questa palestra
        // Per ora tutti i ROLE_GYM_ADMIN possono caricare

        /** @var UploadedFile|null $file */
        $file = $request->files->get('logo');

        if (!$file) {
            return $this->json(['error' => 'File logo obbligatorio'], 400);
        }

        // Validazione tipo file
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json(['error' => 'Solo immagini JPG, PNG, WEBP sono accettate'], 400);
        }

        // Validazione dimensione (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->json(['error' => 'File troppo grande (max 10MB)'], 400);
        }

        try {
            // Imposta il file (VichUploader gestisce lo spostamento)
            $gym->setLogoFile($file);

            $this->entityManager->flush();

            // Ottimizza l'immagine (800x800, qualità 90%)
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/gym-logos/';
            $savedFile = $uploadDir . $gym->getLogo();

            if (file_exists($savedFile)) {
                $this->imageOptimizer->optimizeLogo(new \Symfony\Component\HttpFoundation\File\File($savedFile));
            }

            return $this->json([
                'success' => true,
                'message' => 'Logo caricato con successo',
                'logo_url' => '/uploads/gym-logos/' . $gym->getLogo()
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Errore durante il caricamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina logo palestra
     *
     * DELETE /api/gyms/{id}/logo
     */
    #[Route('/{id}/logo', name: 'api_gym_logo_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_GYM_ADMIN')]
    public function deleteLogo(int $id): JsonResponse
    {
        $gym = $this->gymRepository->find($id);

        if (!$gym) {
            return $this->json(['error' => 'Palestra non trovata'], 404);
        }

        try {
            if (!$gym->getLogo()) {
                return $this->json(['error' => 'Nessun logo da eliminare'], 404);
            }

            // Elimina il file fisico
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/gym-logos/';
            $filePath = $uploadDir . $gym->getLogo();

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Rimuovi dal database
            $gym->setLogo(null);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Logo eliminato con successo'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Errore durante l\'eliminazione: ' . $e->getMessage()
            ], 500);
        }
    }
}
