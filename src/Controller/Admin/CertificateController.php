<?php

namespace App\Controller\Admin;

use App\Domain\Medical\UseCase\GetCertificateById;
use App\Domain\Medical\UseCase\SearchCertificates;
use App\Domain\Medical\UseCase\GetCertificateStats;
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
        private GetCertificateById $getCertificateById,
        private SearchCertificates $searchCertificates,
        private GetCertificateStats $getCertificateStats,
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

        return $this->render('admin/certificates/index.html.twig', [
            'certificates' => $this->searchCertificates->execute($status, $search),
            'stats' => $this->getCertificateStats->execute(),
            'current_status' => $status,
            'current_search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'admin_certificate_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        try {
            $certificate = $this->getCertificateById->execute($id);

            return $this->render('admin/certificates/show.html.twig', [
                'certificate' => $certificate,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_certificates');
        }
    }

    #[Route('/{id}/approve', name: 'admin_certificate_approve', methods: ['POST'])]
    public function approve(int $id): Response
    {
        try {
            $certificate = $this->getCertificateById->execute($id);
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
        try {
            $certificate = $this->getCertificateById->execute($id);
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
