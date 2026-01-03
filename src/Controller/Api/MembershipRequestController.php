<?php

namespace App\Controller\Api;

use App\Domain\Membership\UseCase\RequestMembershipUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * API Controller per richieste iscrizione
 * Usato dall'app mobile
 */
#[Route('/api/membership-requests')]
class MembershipRequestController extends AbstractController
{
    public function __construct(
        private RequestMembershipUseCase $requestMembershipUseCase
    ) {}

    /**
     * Richiedi iscrizione a una palestra tramite slug/QR code
     *
     * POST /api/membership-requests
     * Body: { "gym_slug": "fithub-milano-abc123", "message": "Vorrei iscrivermi..." }
     */
    #[Route('', name: 'api_membership_request_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['gym_slug'])) {
            return $this->json([
                'error' => 'Il campo gym_slug è obbligatorio'
            ], 400);
        }

        try {
            $user = $this->getUser();
            $membershipRequest = $this->requestMembershipUseCase->execute(
                $user,
                $data['gym_slug'],
                $data['message'] ?? null
            );

            return $this->json([
                'success' => true,
                'message' => 'Richiesta inviata con successo. La palestra riceverà una notifica.',
                'request' => [
                    'id' => $membershipRequest->getId(),
                    'gym' => [
                        'id' => $membershipRequest->getGym()->getId(),
                        'name' => $membershipRequest->getGym()->getName(),
                        'address' => $membershipRequest->getGym()->getAddress(),
                        'city' => $membershipRequest->getGym()->getCity(),
                    ],
                    'status' => $membershipRequest->getStatus(),
                    'requested_at' => $membershipRequest->getRequestedAt()->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\DomainException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ottieni le richieste dell'utente loggato
     *
     * GET /api/membership-requests
     */
    #[Route('', name: 'api_membership_request_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myRequests(\App\Domain\Membership\Repository\MembershipRequestRepositoryInterface $requestRepo): JsonResponse
    {
        $user = $this->getUser();

        // Trova tutte le richieste dell'utente
        $requests = $requestRepo->findBy(['user' => $user], ['requestedAt' => 'DESC']);

        $data = array_map(function($request) {
            $result = [
                'id' => $request->getId(),
                'status' => $request->getStatus(),
                'message' => $request->getMessage(),
                'requested_at' => $request->getRequestedAt()->format('Y-m-d H:i:s'),
                'gym' => [
                    'id' => $request->getGym()->getId(),
                    'name' => $request->getGym()->getName(),
                    'address' => $request->getGym()->getAddress(),
                    'city' => $request->getGym()->getCity(),
                ],
            ];

            // Se approvata o rifiutata, aggiungi info admin
            if ($request->getRespondedAt()) {
                $result['responded_at'] = $request->getRespondedAt()->format('Y-m-d H:i:s');
                $result['admin_notes'] = $request->getAdminNotes();
            }

            return $result;
        }, $requests);

        return $this->json([
            'requests' => $data
        ]);
    }

    /**
     * Cerca palestra per slug (per validare QR code prima di richiedere iscrizione)
     *
     * GET /api/membership-requests/validate-gym/{slug}
     */
    #[Route('/validate-gym/{slug}', name: 'api_membership_request_validate_gym', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function validateGym(string $slug, \App\Domain\Gym\Repository\GymRepositoryInterface $gymRepo): JsonResponse
    {
        $gym = $gymRepo->findOneBy(['slug' => $slug]);

        if (!$gym) {
            return $this->json([
                'valid' => false,
                'message' => 'Palestra non trovata'
            ], 404);
        }

        if (!$gym->isActive()) {
            return $this->json([
                'valid' => false,
                'message' => 'Questa palestra non è attualmente attiva'
            ], 400);
        }

        return $this->json([
            'valid' => true,
            'gym' => [
                'id' => $gym->getId(),
                'name' => $gym->getName(),
                'description' => $gym->getDescription(),
                'address' => $gym->getAddress(),
                'city' => $gym->getCity(),
                'postal_code' => $gym->getPostalCode(),
                'phone_number' => $gym->getPhoneNumber(),
                'email' => $gym->getEmail(),
                'logo' => $gym->getLogo(),
            ]
        ]);
    }
}
