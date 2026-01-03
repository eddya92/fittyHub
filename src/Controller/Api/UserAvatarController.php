<?php

namespace App\Controller\Api;

use App\Infrastructure\Service\ImageOptimizerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users')]
class UserAvatarController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ImageOptimizerService $imageOptimizer
    ) {}

    /**
     * Upload avatar utente
     *
     * POST /api/users/avatar
     * Content-Type: multipart/form-data
     * Fields:
     *   - avatar (image file: jpg, jpeg, png, webp)
     */
    #[Route('/avatar', name: 'api_user_avatar_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadAvatar(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('avatar');

        if (!$file) {
            return $this->json(['error' => 'File avatar obbligatorio'], 400);
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
            $user = $this->getUser();

            // Imposta il file (VichUploader gestisce lo spostamento)
            $user->setAvatarFile($file);

            $this->entityManager->flush();

            // Ottimizza l'immagine (400x400, qualità 85%)
            // Recupera il path del file salvato
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/';
            $savedFile = $uploadDir . $user->getProfileImage();

            if (file_exists($savedFile)) {
                $this->imageOptimizer->optimizeAvatar(new \Symfony\Component\HttpFoundation\File\File($savedFile));
            }

            return $this->json([
                'success' => true,
                'message' => 'Avatar caricato con successo',
                'avatar_url' => '/uploads/avatars/' . $user->getProfileImage()
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Errore durante il caricamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina avatar utente
     *
     * DELETE /api/users/avatar
     */
    #[Route('/avatar', name: 'api_user_avatar_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAvatar(): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user->getProfileImage()) {
                return $this->json(['error' => 'Nessun avatar da eliminare'], 404);
            }

            // Elimina il file fisico
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/';
            $filePath = $uploadDir . $user->getProfileImage();

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Rimuovi dal database
            $user->setProfileImage(null);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Avatar eliminato con successo'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Errore durante l\'eliminazione: ' . $e->getMessage()
            ], 500);
        }
    }
}
