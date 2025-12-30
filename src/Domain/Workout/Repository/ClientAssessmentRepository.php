<?php

namespace App\Domain\Workout\Repository;

use App\Domain\Workout\Entity\ClientAssessment;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClientAssessment>
 */
class ClientAssessmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientAssessment::class);
    }

    public function findByPersonalTrainer(PersonalTrainer $pt): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.personalTrainer = :pt')
            ->setParameter('pt', $pt)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByClient(User $client): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.client = :client')
            ->setParameter('client', $client)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestByClient(User $client): ?ClientAssessment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.client = :client')
            ->andWhere('a.status = :status')
            ->setParameter('client', $client)
            ->setParameter('status', 'completed')
            ->orderBy('a.completedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}