<?php

namespace App\Tests\Domain\Invitation\Service;

use App\Domain\Invitation\Entity\PTClientInvitation;
use App\Domain\Invitation\Repository\PTClientInvitationRepository;
use App\Domain\Invitation\Service\PTClientInvitationService;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepository;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PTClientInvitationServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PTClientInvitationRepository $invitationRepo;
    private UserRepository $userRepo;
    private PTClientRelationRepository $relationRepo;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private PTClientInvitationService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->invitationRepo = $this->createMock(PTClientInvitationRepository::class);
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->relationRepo = $this->createMock(PTClientRelationRepository::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->service = new PTClientInvitationService(
            $this->entityManager,
            $this->invitationRepo,
            $this->userRepo,
            $this->relationRepo,
            $this->mailer,
            $this->urlGenerator
        );
    }

    public function testCreateInvitationSuccess(): void
    {
        $trainer = $this->createMock(PersonalTrainer::class);
        $clientEmail = 'client@example.com';

        $this->invitationRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->userRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->mailer
            ->expects($this->once())
            ->method('send');

        $invitation = $this->service->createInvitation($trainer, $clientEmail, 'Test message');

        $this->assertInstanceOf(PTClientInvitation::class, $invitation);
        $this->assertEquals($clientEmail, $invitation->getClientEmail());
    }

    public function testCreateInvitationFailsWhenPendingInvitationExists(): void
    {
        $trainer = $this->createMock(PersonalTrainer::class);
        $clientEmail = 'client@example.com';

        $existingInvitation = $this->createMock(PTClientInvitation::class);
        $existingInvitation->method('isPending')->willReturn(true);

        $this->invitationRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($existingInvitation);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Esiste già un invito pendente per questo cliente');

        $this->service->createInvitation($trainer, $clientEmail);
    }

    public function testAcceptInvitationSuccess(): void
    {
        $token = 'valid-token';
        $invitation = new PTClientInvitation();
        $trainer = $this->createMock(PersonalTrainer::class);
        $client = $this->createMock(User::class);

        // Setup invitation
        $invitation->setPersonalTrainer($trainer);
        $invitation->setClientUser($client);
        $invitation->setStatus('pending');
        $invitation->setClientEmail('client@example.com');

        $this->invitationRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => $token])
            ->willReturn($invitation);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PTClientRelation::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $relation = $this->service->acceptInvitation($token);

        $this->assertInstanceOf(PTClientRelation::class, $relation);
        $this->assertEquals('accepted', $invitation->getStatus());
        $this->assertNotNull($invitation->getRespondedAt());
    }

    public function testAcceptInvitationFailsWhenExpired(): void
    {
        $token = 'expired-token';
        $invitation = new PTClientInvitation();
        $invitation->setExpiresAt(new \DateTimeImmutable('-1 day'));
        $invitation->setStatus('pending');

        $client = $this->createMock(User::class);
        $invitation->setClientUser($client);

        $this->invitationRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($invitation);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Questo invito è scaduto');

        $this->service->acceptInvitation($token);
    }

    public function testDeclineInvitationSuccess(): void
    {
        $token = 'valid-token';
        $invitation = new PTClientInvitation();
        $invitation->setStatus('pending');
        $invitation->setClientEmail('client@example.com');

        $this->invitationRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => $token])
            ->willReturn($invitation);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->service->declineInvitation($token);

        $this->assertEquals('declined', $invitation->getStatus());
        $this->assertNotNull($invitation->getRespondedAt());
    }
}
