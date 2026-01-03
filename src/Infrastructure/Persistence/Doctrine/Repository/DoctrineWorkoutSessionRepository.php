<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Workout\Entity\WorkoutSession;
use App\Domain\Workout\Repository\WorkoutSessionRepositoryInterface;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkoutSession>
 */
class DoctrineWorkoutSessionRepository extends ServiceEntityRepository implements WorkoutSessionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkoutSession::class);
    }

    /**
     * Find recent sessions for a client
     *
     * @return WorkoutSession[]
     */
    public function findRecentByClient(User $client, int $limit = 10): array
    {
        return $this->createQueryBuilder('ws')
            ->andWhere('ws.client = :client')
            ->setParameter('client', $client)
            ->orderBy('ws.sessionDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
