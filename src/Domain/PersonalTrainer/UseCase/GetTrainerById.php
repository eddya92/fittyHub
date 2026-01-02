<?php

namespace App\Domain\PersonalTrainer\UseCase;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;

class GetTrainerById
{
    public function __construct(
        private TrainerRepositoryInterface $trainerRepository
    ) {}

    public function execute(int $id): PersonalTrainer
    {
        $trainer = $this->trainerRepository->find($id);

        if (!$trainer) {
            throw new \RuntimeException('Personal Trainer non trovato.');
        }

        return $trainer;
    }
}
