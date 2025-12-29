<?php

namespace App\Application\Service;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepository;

class TrainerService
{
    public function __construct(
        private PTClientRelationRepository $relationRepository
    ) {}

    /**
     * Ottiene statistiche per un personal trainer
     */
    public function getTrainerStats(PersonalTrainer $trainer): array
    {
        $activeRelations = $this->relationRepository->findBy(
            ['personalTrainer' => $trainer, 'status' => 'active']
        );

        return [
            'total_clients' => $this->relationRepository->count(['personalTrainer' => $trainer]),
            'active_clients' => count($activeRelations),
            'completed_relations' => $this->relationRepository->count([
                'personalTrainer' => $trainer,
                'status' => 'completed'
            ]),
        ];
    }

    /**
     * Ottiene relazioni attive per un trainer
     */
    public function getActiveRelations(PersonalTrainer $trainer): array
    {
        return $this->relationRepository->findBy(
            ['personalTrainer' => $trainer, 'status' => 'active'],
            ['startDate' => 'DESC']
        );
    }
}
