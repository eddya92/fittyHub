<?php

namespace App\Controller\Admin;

use App\Domain\Invitation\UseCase\GetInvitationById;
use App\Domain\Invitation\UseCase\SearchInvitations;
use App\Domain\Invitation\UseCase\GetInvitationStats;
use App\Domain\Invitation\UseCase\CreateInvitation;
use App\Domain\Invitation\UseCase\ResendInvitation;
use App\Domain\Invitation\UseCase\CancelInvitation;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/invitations')]
class InvitationController extends AbstractController
{
    public function __construct(
        private GetInvitationById $getInvitationById,
        private SearchInvitations $searchInvitations,
        private GetInvitationStats $getInvitationStats,
        private CreateInvitation $createInvitation,
        private ResendInvitation $resendInvitation,
        private CancelInvitation $cancelInvitation,
        private GymRepositoryInterface $gymRepository
    ) {}

    #[Route('/', name: 'admin_invitations')]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status', 'pending');
        $search = $request->query->get('search');

        return $this->render('admin/invitations/index.html.twig', [
            'invitations' => $this->searchInvitations->execute($status, $search),
            'stats' => $this->getInvitationStats->execute(),
            'current_status' => $status,
            'current_search' => $search,
        ]);
    }

    #[Route('/create', name: 'admin_invitation_create')]
    public function create(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $message = $request->request->get('message');
            $gymId = $request->request->get('gym_id');

            try {
                $gym = $this->gymRepository->find($gymId);

                if (!$gym) {
                    $this->addFlash('error', 'Palestra non trovata.');
                    return $this->redirectToRoute('admin_invitation_create');
                }

                $this->createInvitation->execute($gym, $email, $message);

                $this->addFlash('success', "Invito inviato con successo a {$email}");
                return $this->redirectToRoute('admin_invitations');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore: ' . $e->getMessage());
            }
        }

        $gyms = $this->gymRepository->findAll();

        return $this->render('admin/invitations/create.html.twig', [
            'gyms' => $gyms,
        ]);
    }

    #[Route('/{id}/resend', name: 'admin_invitation_resend', methods: ['POST'])]
    public function resend(int $id): Response
    {
        try {
            $invitation = $this->getInvitationById->execute($id);
            $this->resendInvitation->execute($invitation);
            $this->addFlash('success', 'Invito reinviato con successo.');
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', 'Errore: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_invitations');
    }

    #[Route('/{id}/cancel', name: 'admin_invitation_cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        try {
            $invitation = $this->getInvitationById->execute($id);
            $this->cancelInvitation->execute($invitation);
            $this->addFlash('success', 'Invito cancellato.');
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', 'Errore: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_invitations');
    }
}
