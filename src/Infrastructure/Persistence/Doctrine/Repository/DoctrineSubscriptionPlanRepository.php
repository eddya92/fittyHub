<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Implementazione Doctrine del SubscriptionPlanRepository
 */
class DoctrineSubscriptionPlanRepository extends ServiceEntityRepository implements SubscriptionPlanRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPlan::class);
    }
}
