<?php

namespace App\Tests\Unit\Domain\Membership\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\UseCase\CreateGymMembershipUseCase;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CreateGymMembershipUseCaseTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private GymRepositoryInterface $gymRepository;
    private MedicalCertificateRepositoryInterface $certificateRepository;
    private CreateGymMembershipUseCase $useCase;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->gymRepository = $this->createMock(GymRepositoryInterface::class);
        $this->certificateRepository = $this->createMock(MedicalCertificateRepositoryInterface::class);

        $this->useCase = new CreateGymMembershipUseCase(
            $this->entityManager,
            $this->gymRepository,
            $this->certificateRepository
        );
    }

    public function testExecuteThrowsExceptionWhenNoValidCertificate(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');

        $this->certificateRepository
            ->expects($this->once())
            ->method('findValidCertificateForUser')
            ->with($user)
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Devi avere un certificato medico valido');

        $this->useCase->execute($user, $gym, $startDate, $endDate);
    }

    public function testExecuteThrowsExceptionWhenActiveMembershipExists(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');

        $certificate = $this->createMock(MedicalCertificate::class);
        $certificate->method('getExpiryDate')
            ->willReturn(new \DateTimeImmutable('2027-01-01'));

        $this->certificateRepository
            ->method('findValidCertificateForUser')
            ->willReturn($certificate);

        $existingMembership = $this->createMock(GymMembership::class);

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'user' => $user,
                'gym' => $gym,
                'status' => 'active'
            ])
            ->willReturn($existingMembership);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(GymMembership::class)
            ->willReturn($repository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Hai già un abbonamento attivo per questa palestra');

        $this->useCase->execute($user, $gym, $startDate, $endDate);
    }

    public function testExecuteThrowsExceptionWhenEndDateBeforeStartDate(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTimeImmutable('2026-12-31');
        $endDate = new \DateTimeImmutable('2026-01-01');

        $certificate = $this->createMock(MedicalCertificate::class);
        $certificate->method('getExpiryDate')
            ->willReturn(new \DateTimeImmutable('2027-01-01'));

        $this->certificateRepository
            ->method('findValidCertificateForUser')
            ->willReturn($certificate);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('La data di fine deve essere successiva alla data di inizio');

        $this->useCase->execute($user, $gym, $startDate, $endDate);
    }

    public function testExecuteThrowsExceptionWhenCertificateExpiresBeforeMembership(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');

        $certificate = $this->createMock(MedicalCertificate::class);
        $certificate->method('getExpiryDate')
            ->willReturn(new \DateTimeImmutable('2026-06-30')); // Scade prima della fine

        $this->certificateRepository
            ->method('findValidCertificateForUser')
            ->willReturn($certificate);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Il tuo certificato medico scade prima della fine dell\'abbonamento');

        $this->useCase->execute($user, $gym, $startDate, $endDate);
    }

    public function testExecuteCreatesValidMembership(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');
        $notes = 'Test membership';

        $certificate = $this->createMock(MedicalCertificate::class);
        $certificate->method('getExpiryDate')
            ->willReturn(new \DateTimeImmutable('2027-01-01')); // Valido per tutto il periodo

        $this->certificateRepository
            ->method('findValidCertificateForUser')
            ->willReturn($certificate);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(GymMembership::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $membership = $this->useCase->execute($user, $gym, $startDate, $endDate, $notes);

        $this->assertInstanceOf(GymMembership::class, $membership);
        $this->assertEquals('active', $membership->getStatus());
        $this->assertEquals($notes, $membership->getNotes());
        $this->assertSame($user, $membership->getUser());
        $this->assertSame($gym, $membership->getGym());
        $this->assertEquals($startDate, $membership->getStartDate());
        $this->assertEquals($endDate, $membership->getEndDate());
        $this->assertSame($certificate, $membership->getMedicalCertificate());
    }

    public function testExecuteCreatesValidMembershipWithoutNotes(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');

        $certificate = $this->createMock(MedicalCertificate::class);
        $certificate->method('getExpiryDate')
            ->willReturn(new \DateTimeImmutable('2027-01-01'));

        $this->certificateRepository
            ->method('findValidCertificateForUser')
            ->willReturn($certificate);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $membership = $this->useCase->execute($user, $gym, $startDate, $endDate);

        $this->assertInstanceOf(GymMembership::class, $membership);
        $this->assertNull($membership->getNotes());
    }
}
