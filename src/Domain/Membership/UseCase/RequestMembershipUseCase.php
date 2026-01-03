<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EmailService;

/**
 * Use Case: Richiesta iscrizione a una palestra
 *
 * L'utente scansiona QR code o inserisce codice palestra
 * e richiede l'iscrizione. L'admin dovrà approvarla.
 */
class RequestMembershipUseCase
{
    public function __construct(
        private MembershipRequestRepositoryInterface $requestRepository,
        private MembershipRepositoryInterface $membershipRepository,
        private GymRepositoryInterface $gymRepository,
        private EmailService $emailService
    ) {}

    /**
     * @throws \DomainException
     */
    public function execute(User $user, string $gymSlug, ?string $message = null): MembershipRequest
    {
        // Trova palestra per slug
        $gym = $this->gymRepository->findOneBy(['slug' => $gymSlug]);

        if (!$gym) {
            throw new \DomainException('Palestra non trovata. Verifica il codice inserito.');
        }

        if (!$gym->isActive()) {
            throw new \DomainException('Questa palestra non è attualmente attiva.');
        }

        // Verifica che non abbia già un abbonamento attivo
        $existingMembership = $this->membershipRepository->findOneBy([
            'user' => $user,
            'gym' => $gym,
            'status' => 'active'
        ]);

        if ($existingMembership) {
            throw new \DomainException('Hai già un abbonamento attivo per questa palestra.');
        }

        // Verifica che non ci sia già una richiesta pendente
        $existingRequest = $this->requestRepository->findPendingRequest($user, $gym);

        if ($existingRequest) {
            throw new \DomainException('Hai già una richiesta di iscrizione in attesa per questa palestra.');
        }

        // Crea richiesta
        $request = new MembershipRequest();
        $request->setUser($user);
        $request->setGym($gym);
        $request->setMessage($message);

        $this->requestRepository->save($request, true);

        // Invia email di conferma all'utente
        $this->emailService->sendMembershipRequestConfirmation($request);

        // Notifica gli admin della palestra
        $this->emailService->sendNewMembershipRequestToAdmins($request);

        return $request;
    }
}
