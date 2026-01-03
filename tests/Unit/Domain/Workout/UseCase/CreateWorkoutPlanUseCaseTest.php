<?php

namespace App\Tests\Unit\Domain\Workout\UseCase;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\Workout\Entity\WorkoutPlan;
use App\Domain\Workout\UseCase\CreateWorkoutPlanUseCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateWorkoutPlanUseCaseTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PTClientRelationRepositoryInterface $relationRepository;
    private CreateWorkoutPlanUseCase $useCase;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->relationRepository = $this->createMock(PTClientRelationRepositoryInterface::class);

        $this->useCase = new CreateWorkoutPlanUseCase(
            $this->entityManager,
            $this->relationRepository
        );
    }

    public function testExecuteThrowsExceptionForInvalidPlanType(): void
    {
        $createdBy = $this->createMock(User::class);
        $targetUser = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Tipo di piano non valido');

        $this->useCase->execute(
            $createdBy,
            $targetUser,
            'Piano Test',
            'invalid_type',
            4,
            $startDate
        );
    }

    public function testExecuteThrowsExceptionWhenTrainerCreatedWithoutTrainer(): void
    {
        $createdBy = $this->createMock(User::class);
        $targetUser = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Devi specificare il Personal Trainer');

        $this->useCase->execute(
            $createdBy,
            $targetUser,
            'Piano Test',
            'trainer_created',
            4,
            $startDate,
            null
        );
    }

    public function testExecuteThrowsExceptionWhenTrainerUserMismatch(): void
    {
        $createdBy = $this->createMock(User::class);
        $targetUser = $this->createMock(User::class);
        $trainerUser = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $trainer = $this->createMock(PersonalTrainer::class);
        $trainer->method('getUser')->willReturn($trainerUser);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Puoi creare piani solo come il tuo account PT');

        $this->useCase->execute(
            $createdBy,
            $targetUser,
            'Piano Test',
            'trainer_created',
            4,
            $startDate,
            $trainer
        );
    }

    public function testExecuteThrowsExceptionWhenNoActiveRelation(): void
    {
        $createdBy = $this->createMock(User::class);
        $targetUser = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $trainer = $this->createMock(PersonalTrainer::class);
        $trainer->method('getUser')->willReturn($createdBy);

        $this->relationRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'personalTrainer' => $trainer,
                'client' => $targetUser,
                'status' => 'active'
            ])
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Non hai una relazione attiva con questo cliente');

        $this->useCase->execute(
            $createdBy,
            $targetUser,
            'Piano Test',
            'trainer_created',
            4,
            $startDate,
            $trainer
        );
    }

    public function testExecuteThrowsExceptionWhenUserCreatedForDifferentUser(): void
    {
        $createdBy = $this->createMock(User::class);
        $targetUser = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Puoi creare piani "user_created" solo per te stesso');

        $this->useCase->execute(
            $createdBy,
            $targetUser,
            'Piano Test',
            'user_created',
            4,
            $startDate
        );
    }

    public function testExecuteThrowsExceptionForInvalidWeeksCount(): void
    {
        $user = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('La durata deve essere tra 1 e 52 settimane');

        $this->useCase->execute(
            $user,
            $user,
            'Piano Test',
            'user_created',
            0,
            $startDate
        );
    }

    public function testExecuteThrowsExceptionForTooManyWeeks(): void
    {
        $user = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('La durata deve essere tra 1 e 52 settimane');

        $this->useCase->execute(
            $user,
            $user,
            'Piano Test',
            'user_created',
            53,
            $startDate
        );
    }

    public function testExecuteCreatesUserCreatedPlan(): void
    {
        $user = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(WorkoutPlan::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $plan = $this->useCase->execute(
            $user,
            $user,
            'Piano di Mario',
            'user_created',
            8,
            $startDate,
            null,
            'Piano personalizzato',
            'Massa muscolare'
        );

        $this->assertInstanceOf(WorkoutPlan::class, $plan);
        $this->assertSame($user, $plan->getClient());
        $this->assertEquals('Piano di Mario', $plan->getName());
        $this->assertEquals('user_created', $plan->getPlanType());
        $this->assertEquals('Piano personalizzato', $plan->getDescription());
        $this->assertEquals('Massa muscolare', $plan->getGoal());
        $this->assertEquals(8, $plan->getWeeksCount());
        $this->assertEquals($startDate, $plan->getStartDate());
        $this->assertNull($plan->getPersonalTrainer());
    }

    public function testExecuteCreatesTrainerCreatedPlan(): void
    {
        $createdBy = $this->createMock(User::class);
        $targetUser = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $trainer = $this->createMock(PersonalTrainer::class);
        $trainer->method('getUser')->willReturn($createdBy);

        $activeRelation = $this->createMock(PTClientRelation::class);

        $this->relationRepository
            ->method('findOneBy')
            ->willReturn($activeRelation);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(WorkoutPlan::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $plan = $this->useCase->execute(
            $createdBy,
            $targetUser,
            'Piano PT',
            'trainer_created',
            12,
            $startDate,
            $trainer,
            'Piano creato dal PT',
            'Forza'
        );

        $this->assertInstanceOf(WorkoutPlan::class, $plan);
        $this->assertSame($targetUser, $plan->getClient());
        $this->assertEquals('Piano PT', $plan->getName());
        $this->assertEquals('trainer_created', $plan->getPlanType());
        $this->assertEquals('Piano creato dal PT', $plan->getDescription());
        $this->assertEquals('Forza', $plan->getGoal());
        $this->assertEquals(12, $plan->getWeeksCount());
        $this->assertSame($trainer, $plan->getPersonalTrainer());
    }

    public function testExecuteCalculatesCorrectEndDate(): void
    {
        $user = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $plan = $this->useCase->execute(
            $user,
            $user,
            'Piano Test',
            'user_created',
            4,
            $startDate
        );

        $expectedEndDate = new \DateTimeImmutable('2026-01-29'); // 4 settimane dopo
        $this->assertEquals($expectedEndDate, $plan->getEndDate());
    }

    public function testExecuteCreatesMinimalUserPlan(): void
    {
        $user = $this->createMock(User::class);
        $startDate = new \DateTimeImmutable('2026-01-01');

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $plan = $this->useCase->execute(
            $user,
            $user,
            'Piano Minimo',
            'user_created',
            1,
            $startDate
        );

        $this->assertInstanceOf(WorkoutPlan::class, $plan);
        $this->assertNull($plan->getDescription());
        $this->assertNull($plan->getGoal());
        $this->assertEquals(1, $plan->getWeeksCount());
    }
}
