<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Use Case: Approva richiesta iscrizione
 *
 * Admin approva la richiesta e crea una GymMembership
 * con date specificate
 */
class ApproveMembershipRequestUseCase
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MembershipRequestRepositoryInterface $requestRepository,
        private CreateGymMembershipUseCase $createMembershipUseCase,
        private EmailService $emailService
    ) {}

    /**
     * @throws \DomainException
     */
    public function execute(
        MembershipRequest $request,
        User $admin,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        ?string $notes = null
    ): GymMembership {
        if (!$request->isPending()) {
            throw new \DomainException('Questa richiesta è già stata processata.');
        }

        // Approva richiesta
        $request->approve($admin);

        // Crea membership usando il UseCase esistente
        // Questo gestisce automaticamente la validazione del certificato medico
        $membership = $this->createMembershipUseCase->execute(
            $request->getUser(),
            $request->getGym(),
            $startDate,
            $endDate,
            $notes
        );

        // Salva richiesta approvata
        $this->requestRepository->save($request, true);

        // Invia email di approvazione all'utente
        $this->emailService->sendMembershipRequestApproved($request, $membership);

        return $membership;
    }
}
