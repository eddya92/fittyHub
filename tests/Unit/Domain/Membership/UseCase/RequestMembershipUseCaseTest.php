<?php

namespace App\Tests\Unit\Domain\Membership\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\Membership\UseCase\RequestMembershipUseCase;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EmailService;
use PHPUnit\Framework\TestCase;

class RequestMembershipUseCaseTest extends TestCase
{
    private MembershipRequestRepositoryInterface $requestRepository;
    private MembershipRepositoryInterface $membershipRepository;
    private GymRepositoryInterface $gymRepository;
    private EmailService $emailService;
    private RequestMembershipUseCase $useCase;

    protected function setUp(): void
    {
        $this->requestRepository = $this->createMock(MembershipRequestRepositoryInterface::class);
        $this->membershipRepository = $this->createMock(MembershipRepositoryInterface::class);
        $this->gymRepository = $this->createMock(GymRepositoryInterface::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->useCase = new RequestMembershipUseCase(
            $this->requestRepository,
            $this->membershipRepository,
            $this->gymRepository,
            $this->emailService
        );
    }

    public function testExecuteThrowsExceptionWhenGymNotFound(): void
    {
        $user = $this->createMock(User::class);
        $gymSlug = 'non-existent-gym';

        $this->gymRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => $gymSlug])
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Palestra non trovata');

        $this->useCase->execute($user, $gymSlug);
    }

    public function testExecuteThrowsExceptionWhenGymNotActive(): void
    {
        $user = $this->createMock(User::class);
        $gymSlug = 'test-gym';

        $gym = $this->createMock(Gym::class);
        $gym->method('isActive')->willReturn(false);

        $this->gymRepository
            ->method('findOneBy')
            ->willReturn($gym);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Questa palestra non è attualmente attiva');

        $this->useCase->execute($user, $gymSlug);
    }

    public function testExecuteThrowsExceptionWhenActiveMembershipExists(): void
    {
        $user = $this->createMock(User::class);
        $gymSlug = 'test-gym';

        $gym = $this->createMock(Gym::class);
        $gym->method('isActive')->willReturn(true);

        $this->gymRepository
            ->method('findOneBy')
            ->willReturn($gym);

        $existingMembership = $this->createMock(GymMembership::class);

        $this->membershipRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'user' => $user,
                'gym' => $gym,
                'status' => 'active'
            ])
            ->willReturn($existingMembership);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Hai già un abbonamento attivo per questa palestra');

        $this->useCase->execute($user, $gymSlug);
    }

    public function testExecuteThrowsExceptionWhenPendingRequestExists(): void
    {
        $user = $this->createMock(User::class);
        $gymSlug = 'test-gym';

        $gym = $this->createMock(Gym::class);
        $gym->method('isActive')->willReturn(true);

        $this->gymRepository
            ->method('findOneBy')
            ->willReturn($gym);

        $this->membershipRepository
            ->method('findOneBy')
            ->willReturn(null);

        $existingRequest = $this->createMock(MembershipRequest::class);

        $this->requestRepository
            ->expects($this->once())
            ->method('findPendingRequest')
            ->with($user, $gym)
            ->willReturn($existingRequest);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Hai già una richiesta di iscrizione in attesa');

        $this->useCase->execute($user, $gymSlug);
    }

    public function testExecuteCreatesRequestAndSendsEmails(): void
    {
        $user = $this->createMock(User::class);
        $gymSlug = 'test-gym';
        $message = 'Vorrei iscrivermi alla palestra';

        $gym = $this->createMock(Gym::class);
        $gym->method('isActive')->willReturn(true);

        $this->gymRepository
            ->method('findOneBy')
            ->willReturn($gym);

        $this->membershipRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->requestRepository
            ->method('findPendingRequest')
            ->willReturn(null);

        $this->requestRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->isInstanceOf(MembershipRequest::class),
                true
            );

        $this->emailService
            ->expects($this->once())
            ->method('sendMembershipRequestConfirmation')
            ->with($this->isInstanceOf(MembershipRequest::class));

        $this->emailService
            ->expects($this->once())
            ->method('sendNewMembershipRequestToAdmins')
            ->with($this->isInstanceOf(MembershipRequest::class));

        $request = $this->useCase->execute($user, $gymSlug, $message);

        $this->assertInstanceOf(MembershipRequest::class, $request);
        $this->assertSame($user, $request->getUser());
        $this->assertSame($gym, $request->getGym());
        $this->assertEquals($message, $request->getMessage());
    }

    public function testExecuteCreatesRequestWithoutMessage(): void
    {
        $user = $this->createMock(User::class);
        $gymSlug = 'test-gym';

        $gym = $this->createMock(Gym::class);
        $gym->method('isActive')->willReturn(true);

        $this->gymRepository
            ->method('findOneBy')
            ->willReturn($gym);

        $this->membershipRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->requestRepository
            ->method('findPendingRequest')
            ->willReturn(null);

        $this->requestRepository
            ->expects($this->once())
            ->method('save');

        $this->emailService
            ->expects($this->once())
            ->method('sendMembershipRequestConfirmation');

        $this->emailService
            ->expects($this->once())
            ->method('sendNewMembershipRequestToAdmins');

        $request = $this->useCase->execute($user, $gymSlug);

        $this->assertInstanceOf(MembershipRequest::class, $request);
        $this->assertNull($request->getMessage());
    }
}
