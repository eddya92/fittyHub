<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;

/**
 * Use Case: Ottiene tutti i piani abbonamento
 */
class GetAllSubscriptionPlans
{
    public function __construct(
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    /**
     * @return array<SubscriptionPlan>
     */
    public function execute(): array
    {
        return $this->planRepository->findAll();
    }
}
