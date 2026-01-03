<?php

namespace App\Tests\Unit\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\Membership\UseCase\RejectMembershipRequestUseCase;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EmailService;
use PHPUnit\Framework\TestCase;

class RejectMembershipRequestUseCaseTest extends TestCase
{
    private MembershipRequestRepositoryInterface $requestRepository;
    private EmailService $emailService;
    private RejectMembershipRequestUseCase $useCase;

    protected function setUp(): void
    {
        $this->requestRepository = $this->createMock(MembershipRequestRepositoryInterface::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->useCase = new RejectMembershipRequestUseCase(
            $this->requestRepository,
            $this->emailService
        );
    }

    public function testExecuteThrowsExceptionWhenRequestNotPending(): void
    {
        $request = $this->createMock(MembershipRequest::class);
        $request->method('isPending')->willReturn(false);

        $admin = $this->createMock(User::class);
        $reason = 'Documentazione incompleta';

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Questa richiesta è già stata processata');

        $this->useCase->execute($request, $admin, $reason);
    }

    public function testExecuteRejectsRequestWithReason(): void
    {
        $admin = $this->createMock(User::class);
        $request = $this->createMock(MembershipRequest::class);
        $request->method('isPending')->willReturn(true);

        $reason = 'Documentazione incompleta';

        // Verifica che la richiesta venga rifiutata
        $request
            ->expects($this->once())
            ->method('reject')
            ->with($admin, $reason);

        // Verifica che la richiesta venga salvata
        $this->requestRepository
            ->expects($this->once())
            ->method('save')
            ->with($request, true);

        // Verifica che venga inviata l'email
        $this->emailService
            ->expects($this->once())
            ->method('sendMembershipRequestRejected')
            ->with($request);

        $this->useCase->execute($request, $admin, $reason);
    }

    public function testExecuteRejectsRequestWithoutReason(): void
    {
        $admin = $this->createMock(User::class);
        $request = $this->createMock(MembershipRequest::class);
        $request->method('isPending')->willReturn(true);

        // Verifica che reject venga chiamato con null reason
        $request
            ->expects($this->once())
            ->method('reject')
            ->with($admin, null);

        $this->requestRepository
            ->expects($this->once())
            ->method('save')
            ->with($request, true);

        $this->emailService
            ->expects($this->once())
            ->method('sendMembershipRequestRejected')
            ->with($request);

        $this->useCase->execute($request, $admin);
    }

    public function testExecuteHandlesMultipleRejectionsCorrectly(): void
    {
        $admin = $this->createMock(User::class);

        // Prima richiesta - pending
        $request1 = $this->createMock(MembershipRequest::class);
        $request1->method('isPending')->willReturn(true);
        $request1->expects($this->once())->method('reject');

        // Seconda richiesta - non pending (già processata)
        $request2 = $this->createMock(MembershipRequest::class);
        $request2->method('isPending')->willReturn(false);
        $request2->expects($this->never())->method('reject');

        $this->requestRepository
            ->expects($this->once())
            ->method('save');

        $this->emailService
            ->expects($this->once())
            ->method('sendMembershipRequestRejected');

        // Prima richiesta deve passare
        $this->useCase->execute($request1, $admin, 'Test reason');

        // Seconda richiesta deve fallire
        $this->expectException(\DomainException::class);
        $this->useCase->execute($request2, $admin, 'Test reason');
    }
}
