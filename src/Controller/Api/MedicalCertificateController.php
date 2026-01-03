<?php

namespace App\Controller\Api;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/medical-certificates')]
class MedicalCertificateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MedicalCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * Upload certificato medico
     *
     * POST /api/medical-certificates
     * Content-Type: multipart/form-data
     * Fields:
     *   - certificate_file (PDF file)
     *   - certificate_type (string)
     *   - issue_date (Y-m-d)
     *   - expiry_date (Y-m-d)
     *   - doctor_name (string)
     *   - doctor_number (string, optional)
     */
    #[Route('', name: 'api_medical_certificate_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('certificate_file');

        if (!$file) {
            return $this->json(['error' => 'File certificato obbligatorio'], 400);
        }

        // Validazione tipo file
        if (!in_array($file->getMimeType(), ['application/pdf'])) {
            return $this->json(['error' => 'Solo file PDF sono accettati'], 400);
        }

        // Validazione dimensione (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json(['error' => 'File troppo grande (max 5MB)'], 400);
        }

        try {
            $certificate = new MedicalCertificate();
            $certificate->setUser($this->getUser());
            $certificate->setCertificateType($request->request->get('certificate_type', 'agonistica'));
            $certificate->setIssueDate(new \DateTime($request->request->get('issue_date')));
            $certificate->setExpiryDate(new \DateTime($request->request->get('expiry_date')));
            $certificate->setDoctorName($request->request->get('doctor_name'));
            $certificate->setDoctorNumber($request->request->get('doctor_number'));
            $certificate->setCertificateFile($file);

            $this->entityManager->persist($certificate);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Certificato caricato con successo. In attesa di approvazione.',
                'certificate' => [
                    'id' => $certificate->getId(),
                    'status' => $certificate->getStatus(),
                    'certificate_type' => $certificate->getCertificateType(),
                    'issue_date' => $certificate->getIssueDate()->format('Y-m-d'),
                    'expiry_date' => $certificate->getExpiryDate()->format('Y-m-d'),
                    'uploaded_at' => $certificate->getUploadedAt()->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Errore durante il caricamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista certificati dell'utente loggato
     *
     * GET /api/medical-certificates
     */
    #[Route('', name: 'api_medical_certificate_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(): JsonResponse
    {
        $certificates = $this->certificateRepository->findBy(
            ['user' => $this->getUser()],
            ['uploadedAt' => 'DESC']
        );

        $data = array_map(function($cert) {
            return [
                'id' => $cert->getId(),
                'status' => $cert->getStatus(),
                'certificate_type' => $cert->getCertificateType(),
                'issue_date' => $cert->getIssueDate()->format('Y-m-d'),
                'expiry_date' => $cert->getExpiryDate()->format('Y-m-d'),
                'doctor_name' => $cert->getDoctorName(),
                'uploaded_at' => $cert->getUploadedAt()->format('Y-m-d H:i:s'),
                'is_expired' => $cert->isExpired(),
                'file_url' => $cert->getFilePath() ? '/uploads/medical-certificates/' . $cert->getFilePath() : null,
            ];
        }, $certificates);

        return $this->json(['certificates' => $data]);
    }

    /**
     * Dettaglio certificato
     *
     * GET /api/medical-certificates/{id}
     */
    #[Route('/{id}', name: 'api_medical_certificate_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id): JsonResponse
    {
        $certificate = $this->certificateRepository->find($id);

        if (!$certificate) {
            return $this->json(['error' => 'Certificato non trovato'], 404);
        }

        // Verifica che l'utente sia il proprietario
        if ($certificate->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Non autorizzato'], 403);
        }

        return $this->json([
            'certificate' => [
                'id' => $certificate->getId(),
                'status' => $certificate->getStatus(),
                'certificate_type' => $certificate->getCertificateType(),
                'issue_date' => $certificate->getIssueDate()->format('Y-m-d'),
                'expiry_date' => $certificate->getExpiryDate()->format('Y-m-d'),
                'doctor_name' => $certificate->getDoctorName(),
                'doctor_number' => $certificate->getDoctorNumber(),
                'uploaded_at' => $certificate->getUploadedAt()->format('Y-m-d H:i:s'),
                'reviewed_at' => $certificate->getReviewedAt()?->format('Y-m-d H:i:s'),
                'notes' => $certificate->getNotes(),
                'is_expired' => $certificate->isExpired(),
                'file_url' => $certificate->getFilePath() ? '/uploads/medical-certificates/' . $certificate->getFilePath() : null,
            ]
        ]);
    }
}
