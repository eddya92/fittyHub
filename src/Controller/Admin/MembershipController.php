<?php

namespace App\Controller\Admin;

use App\Application\Service\MembershipService;
use App\Domain\Membership\Repository\GymMembershipRepository;
use App\Domain\Medical\Repository\MedicalCertificateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/memberships')]
class MembershipController extends AbstractController
{
    public function __construct(
        private MembershipService $membershipService,
        private GymMembershipRepository $membershipRepository,
        private MedicalCertificateRepository $certificateRepository
    ) {}

    #[Route('/', name: 'admin_memberships')]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $gym = $request->query->get('gym');

        $memberships = $this->membershipRepository->findWithFilters($status, $search, $gym);
        $stats = $this->membershipService->getStats();

        return $this->render('admin/memberships/index.html.twig', [
            'memberships' => $memberships,
            'stats' => $stats,
            'current_status' => $status,
            'current_search' => $search,
            'current_gym' => $gym,
        ]);
    }

    #[Route('/{id}', name: 'admin_membership_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        // Cerca il certificato medico più recente dell'utente
        $certificate = $this->certificateRepository->findOneBy(
            ['user' => $membership->getUser()],
            ['uploadedAt' => 'DESC']
        );

        return $this->render('admin/memberships/show.html.twig', [
            'membership' => $membership,
            'certificate' => $certificate,
        ]);
    }

    #[Route('/{id}/cancel', name: 'admin_membership_cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        try {
            $this->membershipService->cancelMembership($membership);
            $this->addFlash('success', 'Iscrizione cancellata con successo.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    #[Route('/{id}/reactivate', name: 'admin_membership_reactivate', methods: ['POST'])]
    public function reactivate(int $id): Response
    {
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        try {
            $this->membershipService->reactivateMembership($membership);
            $this->addFlash('success', 'Iscrizione riattivata con successo.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    #[Route('/{id}/edit', name: 'admin_membership_edit')]
    public function edit(int $id, Request $request): Response
    {
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        if ($request->isMethod('POST')) {
            try {
                $this->membershipService->updateMembershipAndUser($membership, $request->request->all());
                $this->addFlash('success', 'Dati aggiornati con successo.');
                return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore: ' . $e->getMessage());
            }
        }

        return $this->render('admin/memberships/edit.html.twig', [
            'membership' => $membership,
        ]);
    }
}
