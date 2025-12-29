<?php

namespace App\Controller;

use App\Domain\Invitation\Service\GymPTInvitationService;
use App\Domain\Invitation\Service\PTClientInvitationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    #[Route('/invitation/pt-client/{token}/accept', name: 'app_invitation_pt_client_accept')]
    public function acceptPTClientInvitation(
        string $token,
        PTClientInvitationService $invitationService
    ): Response {
        try {
            $relation = $invitationService->acceptInvitation($token);

            $this->addFlash('success', 'Hai accettato l\'invito! Ora sei seguito da un Personal Trainer.');

            return $this->redirectToRoute('app_dashboard');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->render('invitation/error.html.twig', [
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('/invitation/pt-client/{token}/decline', name: 'app_invitation_pt_client_decline')]
    public function declinePTClientInvitation(
        string $token,
        PTClientInvitationService $invitationService
    ): Response {
        try {
            $invitationService->declineInvitation($token);

            return $this->render('invitation/declined.html.twig', [
                'type' => 'PT Client'
            ]);
        } catch (\DomainException $e) {
            return $this->render('invitation/error.html.twig', [
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('/invitation/gym-pt/{token}/accept', name: 'app_invitation_gym_pt_accept')]
    public function acceptGymPTInvitation(
        string $token,
        GymPTInvitationService $invitationService
    ): Response {
        try {
            $trainer = $invitationService->acceptInvitation($token);

            $this->addFlash('success', 'Hai accettato la collaborazione! Ora sei un PT della palestra.');

            return $this->redirectToRoute('app_dashboard');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->render('invitation/error.html.twig', [
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('/invitation/gym-pt/{token}/decline', name: 'app_invitation_gym_pt_decline')]
    public function declineGymPTInvitation(
        string $token,
        GymPTInvitationService $invitationService
    ): Response {
        try {
            $invitationService->declineInvitation($token);

            return $this->render('invitation/declined.html.twig', [
                'type' => 'Gym PT'
            ]);
        } catch (\DomainException $e) {
            return $this->render('invitation/error.html.twig', [
                'message' => $e->getMessage()
            ]);
        }
    }
}
