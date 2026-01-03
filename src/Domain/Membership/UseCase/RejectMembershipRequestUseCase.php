<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EmailService;

/**
 * Use Case: Rifiuta richiesta iscrizione
 *
 * Admin rifiuta la richiesta con motivazione
 */
class RejectMembershipRequestUseCase
{
    public function __construct(
        private MembershipRequestRepositoryInterface $requestRepository,
        private EmailService $emailService
    ) {}

    /**
     * @throws \DomainException
     */
    public function execute(
        MembershipRequest $request,
        User $admin,
        ?string $reason = null
    ): void {
        if (!$request->isPending()) {
            throw new \DomainException('Questa richiesta è già stata processata.');
        }

        $request->reject($admin, $reason);

        $this->requestRepository->save($request, true);

        // Invia email di rifiuto all'utente
        $this->emailService->sendMembershipRequestRejected($request);
    }
}
