<?php

namespace App\Domain\PersonalTrainer\UseCase;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Use Case: Assegna un Personal Trainer a un cliente
 */
class AssignTrainerToClient
{
    public function __construct(
        private PTClientRelationRepositoryInterface $relationRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws \RuntimeException se esiste già una relazione attiva
     */
    public function execute(
        PersonalTrainer $trainer,
        User $client,
        ?string $notes = null
    ): PTClientRelation {
        // Verifica relazione esistente
        $existingRelation = $this->relationRepository->findOneBy([
            'personalTrainer' => $trainer,
            'client' => $client,
            'status' => 'active'
        ]);

        if ($existingRelation) {
            throw new \RuntimeException('Questo cliente è già assegnato a questo PT.');
        }

        $relation = new PTClientRelation();
        $relation->setPersonalTrainer($trainer);
        $relation->setClient($client);
        $relation->setStatus('active');
        $relation->setStartDate(new \DateTime());
        $relation->setNotes($notes);

        $this->entityManager->persist($relation);
        $this->entityManager->flush();

        return $relation;
    }
}
