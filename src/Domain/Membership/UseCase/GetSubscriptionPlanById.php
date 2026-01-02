<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;

/**
 * Use Case: Ottiene un piano abbonamento per ID
 */
class GetSubscriptionPlanById
{
    public function __construct(
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    /**
     * @throws \RuntimeException se il piano non esiste
     */
    public function execute(int $id): SubscriptionPlan
    {
        $plan = $this->planRepository->find($id);

        if (!$plan) {
            throw new \RuntimeException('Piano abbonamento non trovato.');
        }

        return $plan;
    }
}
