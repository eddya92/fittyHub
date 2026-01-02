<?php

namespace App\Domain\PersonalTrainer\UseCase;

use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;

class SearchTrainers
{
    public function __construct(
        private TrainerRepositoryInterface $trainerRepository
    ) {}

    public function execute(?string $search, ?string $specialization): array
    {
        return $this->trainerRepository->findWithFilters($search, $specialization);
    }
}
