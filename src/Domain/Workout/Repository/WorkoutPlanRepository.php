<?php

namespace App\Domain\Workout\Repository;

use App\Domain\Workout\Entity\WorkoutPlan;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkoutPlan>
 */
class WorkoutPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkoutPlan::class);
    }

    /**
     * Find active plans for a client
     *
     * @return WorkoutPlan[]
     */
    public function findActiveByClient(User $client): array
    {
        return $this->createQueryBuilder('wp')
            ->andWhere('wp.client = :client')
            ->andWhere('wp.isActive = :active')
            ->setParameter('client', $client)
            ->setParameter('active', true)
            ->orderBy('wp.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find plans created by a PT
     *
     * @return WorkoutPlan[]
     */
    public function findByPersonalTrainer(PersonalTrainer $pt): array
    {
        return $this->createQueryBuilder('wp')
            ->andWhere('wp.personalTrainer = :pt')
            ->setParameter('pt', $pt)
            ->orderBy('wp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find template plans created by a PT
     *
     * @return WorkoutPlan[]
     */
    public function findTemplatesByPT(PersonalTrainer $pt): array
    {
        return $this->createQueryBuilder('wp')
            ->andWhere('wp.personalTrainer = :pt')
            ->andWhere('wp.isTemplate = :template')
            ->setParameter('pt', $pt)
            ->setParameter('template', true)
            ->orderBy('wp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
