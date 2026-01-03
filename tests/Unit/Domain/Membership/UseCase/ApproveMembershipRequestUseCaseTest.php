<?php

namespace App\Tests\Unit\Domain\Membership\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\Membership\UseCase\ApproveMembershipRequestUseCase;
use App\Domain\Membership\UseCase\CreateGymMembershipUseCase;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ApproveMembershipRequestUseCaseTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private MembershipRequestRepositoryInterface $requestRepository;
    private CreateGymMembershipUseCase $createMembershipUseCase;
    private EmailService $emailService;
    private ApproveMembershipRequestUseCase $useCase;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->requestRepository = $this->createMock(MembershipRequestRepositoryInterface::class);
        $this->createMembershipUseCase = $this->createMock(CreateGymMembershipUseCase::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->useCase = new ApproveMembershipRequestUseCase(
            $this->entityManager,
            $this->requestRepository,
            $this->createMembershipUseCase,
            $this->emailService
        );
    }

    public function testExecuteThrowsExceptionWhenRequestNotPending(): void
    {
        $request = $this->createMock(MembershipRequest::class);
        $request->method('isPending')->willReturn(false);

        $admin = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Questa richiesta è già stata processata');

        $this->useCase->execute($request, $admin, $startDate, $endDate);
    }

    public function testExecuteApprovesRequestAndCreatesMembership(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $admin = $this->createMock(User::class);

        $request = $this->createMock(MembershipRequest::class);
        $request->method('isPending')->willReturn(true);
        $request->method('getUser')->willReturn($user);
        $request->method('getGym')->willReturn($gym);

        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');
        $notes = 'Approved membership';

        $membership = $this->createMock(GymMembership::class);

        // Verifica che la richiesta venga approvata
        $request
            ->expects($this->once())
            ->method('approve')
            ->with($admin);

        // Verifica che venga creato il membership
        $this->createMembershipUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($user, $gym, $startDate, $endDate, $notes)
            ->willReturn($membership);

        // Verifica che la richiesta venga salvata
        $this->requestRepository
            ->expects($this->once())
            ->method('save')
            ->with($request, true);

        // Verifica che venga inviata l'email
        $this->emailService
            ->expects($this->once())
            ->method('sendMembershipRequestApproved')
            ->with($request, $membership);

        $result = $this->useCase->execute($request, $admin, $startDate, $endDate, $notes);

        $this->assertSame($membership, $result);
    }

    public function testExecuteApprovesRequestWithoutNotes(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $admin = $this->createMock(User::class);

        $request = $this->createMock(MembershipRequest::class);
        $request->method('isPending')->willReturn(true);
        $request->method('getUser')->willReturn($user);
        $request->method('getGym')->willReturn($gym);

        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');

        $membership = $this->createMock(GymMembership::class);

        $request->expects($this->once())->method('approve')->with($admin);

        $this->createMembershipUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($user, $gym, $startDate, $endDate, null)
            ->willReturn($membership);

        $this->requestRepository
            ->expects($this->once())
            ->method('save')
            ->with($request, true);

        $this->emailService
            ->expects($this->once())
            ->method('sendMembershipRequestApproved');

        $result = $this->useCase->execute($request, $admin, $startDate, $endDate);

        $this->assertSame($membership, $result);
    }

    public function testExecutePropagatesCreateMembershipExceptions(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $admin = $this->createMock(User::class);

        $request = $this->createMock(MembershipRequest::class);
        $request->method('isPending')->willReturn(true);
        $request->method('getUser')->willReturn($user);
        $request->method('getGym')->willReturn($gym);

        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');

        $request->expects($this->once())->method('approve');

        // Simula un errore nella creazione del membership (es. certificato mancante)
        $this->createMembershipUseCase
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \DomainException('Certificato medico mancante'));

        // La richiesta non dovrebbe essere salvata se c'è un errore
        $this->requestRepository
            ->expects($this->never())
            ->method('save');

        $this->emailService
            ->expects($this->never())
            ->method('sendMembershipRequestApproved');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Certificato medico mancante');

        $this->useCase->execute($request, $admin, $startDate, $endDate);
    }
}
