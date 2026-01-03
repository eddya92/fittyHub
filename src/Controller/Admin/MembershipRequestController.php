<?php

namespace App\Controller\Admin;

use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\Membership\UseCase\ApproveMembershipRequestUseCase;
use App\Domain\Membership\UseCase\RejectMembershipRequestUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller Admin per gestire richieste iscrizione
 */
#[Route('/admin/membership-requests')]
class MembershipRequestController extends AbstractController
{
    public function __construct(
        private MembershipRequestRepositoryInterface $requestRepository,
        private ApproveMembershipRequestUseCase $approveUseCase,
        private RejectMembershipRequestUseCase $rejectUseCase
    ) {}

    /**
     * Lista richieste iscrizione pendenti
     */
    #[Route('', name: 'admin_membership_requests')]
    public function index(): Response
    {
        // TODO: filtrare per palestra dell'admin loggato
        $requests = $this->requestRepository->findBy(
            ['status' => 'pending'],
            ['requestedAt' => 'DESC']
        );

        return $this->render('admin/membership_requests/index.html.twig', [
            'requests' => $requests,
        ]);
    }

    /**
     * Dettaglio richiesta
     */
    #[Route('/{id}', name: 'admin_membership_request_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $request = $this->requestRepository->find($id);

        if (!$request) {
            $this->addFlash('error', 'Richiesta non trovata.');
            return $this->redirectToRoute('admin_membership_requests');
        }

        return $this->render('admin/membership_requests/show.html.twig', [
            'request' => $request,
        ]);
    }

    /**
     * Approva richiesta
     */
    #[Route('/{id}/approve', name: 'admin_membership_request_approve', methods: ['POST'])]
    public function approve(int $id, Request $request): Response
    {
        $membershipRequest = $this->requestRepository->find($id);

        if (!$membershipRequest) {
            $this->addFlash('error', 'Richiesta non trovata.');
            return $this->redirectToRoute('admin_membership_requests');
        }

        try {
            // Date dall'admin
            $startDate = new \DateTimeImmutable($request->request->get('start_date'));
            $endDate = new \DateTimeImmutable($request->request->get('end_date'));
            $notes = $request->request->get('notes');

            // Approva e crea membership
            $membership = $this->approveUseCase->execute(
                $membershipRequest,
                $this->getUser(),
                $startDate,
                $endDate,
                $notes
            );

            $this->addFlash('success', 'Richiesta approvata! Abbonamento creato con successo.');
            return $this->redirectToRoute('admin_membership_show', ['id' => $membership->getId()]);

        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_membership_request_show', ['id' => $id]);
        }
    }

    /**
     * Rifiuta richiesta
     */
    #[Route('/{id}/reject', name: 'admin_membership_request_reject', methods: ['POST'])]
    public function reject(int $id, Request $request): Response
    {
        $membershipRequest = $this->requestRepository->find($id);

        if (!$membershipRequest) {
            $this->addFlash('error', 'Richiesta non trovata.');
            return $this->redirectToRoute('admin_membership_requests');
        }

        try {
            $reason = $request->request->get('reason');

            $this->rejectUseCase->execute(
                $membershipRequest,
                $this->getUser(),
                $reason
            );

            $this->addFlash('success', 'Richiesta rifiutata.');
            return $this->redirectToRoute('admin_membership_requests');

        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_membership_request_show', ['id' => $id]);
        }
    }
}
