<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;

/**
 * Use Case: Attiva/disattiva un piano abbonamento
 */
class ToggleSubscriptionPlan
{
    public function __construct(
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    public function execute(SubscriptionPlan $plan): void
    {
        $plan->setIsActive(!$plan->isActive());

        $this->planRepository->save($plan, true);
    }
}
