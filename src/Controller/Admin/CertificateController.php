<?php

namespace App\Controller\Admin;

use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\Medical\UseCase\ApproveCertificate;
use App\Domain\Medical\UseCase\RejectCertificate;
use App\Domain\Medical\UseCase\UploadCertificate;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/certificates')]
class CertificateController extends AbstractController
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository,
        private ApproveCertificate $approveCertificate,
        private RejectCertificate $rejectCertificate,
        private UploadCertificate $uploadCertificate,
        private UserRepositoryInterface $userRepository
    ) {}

    #[Route('/', name: 'admin_certificates')]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status', 'pending_review');
        $search = $request->query->get('search');

        $certificates = $this->certificateRepository->findBy(['status' => $status]);

        $allCertificates = $this->certificateRepository->findAll();
        $stats = [
            'total' => count($allCertificates),
            'pending' => count(array_filter($allCertificates, fn($c) => $c->getStatus() === 'pending_review')),
            'approved' => count(array_filter($allCertificates, fn($c) => $c->getStatus() === 'approved')),
            'rejected' => count(array_filter($allCertificates, fn($c) => $c->getStatus() === 'rejected')),
        ];

        return $this->render('admin/certificates/index.html.twig', [
            'certificates' => $certificates,
            'stats' => $stats,
            'current_status' => $status,
            'current_search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'admin_certificate_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $certificate = $this->certificateRepository->find($id);

        if (!$certificate) {
            $this->addFlash('error', 'Certificato non trovato.');
            return $this->redirectToRoute('admin_certificates');
        }

        return $this->render('admin/certificates/show.html.twig', [
            'certificate' => $certificate,
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_certificate_approve', methods: ['POST'])]
    public function approve(int $id): Response
    {
        $certificate = $this->certificateRepository->find($id);

        if (!$certificate) {
            $this->addFlash('error', 'Certificato non trovato.');
            return $this->redirectToRoute('admin_certificates');
        }

        try {
            $this->approveCertificate->execute($certificate);
            $this->addFlash('success', 'Certificato approvato con successo.');
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_certificates');
    }

    #[Route('/{id}/reject', name: 'admin_certificate_reject', methods: ['POST'])]
    public function reject(int $id): Response
    {
        $certificate = $this->certificateRepository->find($id);

        if (!$certificate) {
            $this->addFlash('error', 'Certificato non trovato.');
            return $this->redirectToRoute('admin_certificates');
        }

        try {
            $this->rejectCertificate->execute($certificate);
            $this->addFlash('success', 'Certificato rifiutato.');
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_certificates');
    }

    #[Route('/upload/{userId}', name: 'admin_certificate_upload')]
    public function upload(int $userId, Request $request): Response
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utente non trovato.');
            return $this->redirectToRoute('admin_memberships');
        }

        if ($request->isMethod('POST')) {
            $file = $request->files->get('certificate_file');
            $certificateType = $request->request->get('certificate_type');
            $expiryDate = $request->request->get('expiry_date');
            $notes = $request->request->get('notes');

            if (!$file) {
                $this->addFlash('error', 'Seleziona un file PDF.');
                return $this->redirectToRoute('admin_certificate_upload', ['userId' => $userId]);
            }

            if (!$certificateType || !$expiryDate) {
                $this->addFlash('error', 'Compila tutti i campi obbligatori.');
                return $this->redirectToRoute('admin_certificate_upload', ['userId' => $userId]);
            }

            try {
                $this->uploadCertificate->execute(
                    $user,
                    $file,
                    $certificateType,
                    $expiryDate,
                    $notes
                );

                $this->addFlash('success', 'Certificato caricato e approvato con successo.');

                return $this->redirectToRoute('admin_memberships', ['search' => $user->getEmail()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore durante il caricamento: ' . $e->getMessage());
            }
        }

        return $this->render('admin/certificates/upload.html.twig', [
            'user' => $user,
        ]);
    }
}
