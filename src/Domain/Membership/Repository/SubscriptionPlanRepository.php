<?php

namespace App\Domain\Membership\Repository;

use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Gym\Entity\Gym;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubscriptionPlan>
 */
class SubscriptionPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPlan::class);
    }

    /**
     * Find active plans for a gym
     *
     * @return SubscriptionPlan[]
     */
    public function findActiveByGym(Gym $gym): array
    {
        return $this->createQueryBuilder('sp')
            ->andWhere('sp.gym = :gym')
            ->andWhere('sp.isActive = :active')
            ->setParameter('gym', $gym)
            ->setParameter('active', true)
            ->orderBy('sp.price', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
