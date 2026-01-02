<?php

namespace App\Domain\Workout\UseCase;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\Workout\Entity\WorkoutPlan;
use Doctrine\ORM\EntityManagerInterface;

class CreateWorkoutPlanUseCase
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PTClientRelationRepositoryInterface $relationRepository
    ) {
    }

    public function execute(
        User $createdBy,
        User $targetUser,
        string $name,
        string $planType,
        int $weeksCount,
        \DateTimeImmutable $startDate,
        ?PersonalTrainer $trainer = null,
        ?string $description = null,
        ?string $goal = null
    ): WorkoutPlan {
        // Business Rule 1: Valida planType
        if (!in_array($planType, ['user_created', 'trainer_created'])) {
            throw new \DomainException('Tipo di piano non valido');
        }

        // Business Rule 2: Se trainer_created, verifica che esista relazione PT-Cliente attiva
        if ($planType === 'trainer_created') {
            if (!$trainer) {
                throw new \DomainException('Devi specificare il Personal Trainer per piani "trainer_created"');
            }

            if ($trainer->getUser() !== $createdBy) {
                throw new \DomainException('Puoi creare piani solo come il tuo account PT');
            }

            $activeRelation = $this->relationRepository->findOneBy([
                'personalTrainer' => $trainer,
                'client' => $targetUser,
                'status' => 'active'
            ]);

            if (!$activeRelation) {
                throw new \DomainException(
                    'Non hai una relazione attiva con questo cliente. ' .
                    'Il cliente deve prima accettare il tuo invito.'
                );
            }
        }

        // Business Rule 3: Se user_created, solo l'utente può creare per se stesso
        if ($planType === 'user_created' && $createdBy !== $targetUser) {
            throw new \DomainException('Puoi creare piani "user_created" solo per te stesso');
        }

        // Business Rule 4: Validazione date e durata
        if ($weeksCount < 1 || $weeksCount > 52) {
            throw new \DomainException('La durata deve essere tra 1 e 52 settimane');
        }

        $endDate = $startDate->modify("+{$weeksCount} weeks");

        // Crea il piano
        $workoutPlan = new WorkoutPlan();
        $workoutPlan->setUser($targetUser);
        $workoutPlan->setName($name);
        $workoutPlan->setDescription($description);
        $workoutPlan->setPlanType($planType);
        $workoutPlan->setGoal($goal);
        $workoutPlan->setWeeksCount($weeksCount);
        $workoutPlan->setStartDate($startDate);
        $workoutPlan->setEndDate($endDate);

        if ($planType === 'trainer_created') {
            $workoutPlan->setPersonalTrainer($trainer);
        }

        $this->entityManager->persist($workoutPlan);
        $this->entityManager->flush();

        return $workoutPlan;
    }
}
