<?php

namespace App\Domain\Workout\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Workout\Entity\WorkoutPlan;
use App\Domain\Workout\UseCase\CreateWorkoutPlanUseCase;
use Symfony\Bundle\SecurityBundle\Security;

final class WorkoutPlanProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly Security $security,
        private readonly CreateWorkoutPlanUseCase $createWorkoutPlanUseCase
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof WorkoutPlan) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        // Se nuovo piano, usa il UseCase per validazioni business
        if (!$data->getId()) {
            $user = $this->security->getUser();
            if (!$user) {
                throw new \RuntimeException('Devi essere autenticato per creare un piano');
            }

            try {
                $targetUser = $data->getUser() ?? $user;

                $workoutPlan = $this->createWorkoutPlanUseCase->execute(
                    createdBy: $user,
                    targetUser: $targetUser,
                    name: $data->getName(),
                    planType: $data->getPlanType(),
                    weeksCount: $data->getWeeksCount(),
                    startDate: $data->getStartDate(),
                    trainer: $data->getPersonalTrainer(),
                    description: $data->getDescription(),
                    goal: $data->getGoal()
                );

                return $workoutPlan;
            } catch (\DomainException $e) {
                throw new \RuntimeException($e->getMessage(), 400, $e);
            }
        }

        // Per aggiornamenti, usa il processor standard
        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
        $data->setUpdatedAt(new \DateTimeImmutable());

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
