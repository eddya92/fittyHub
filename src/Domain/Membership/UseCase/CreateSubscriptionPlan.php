<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;

/**
 * Use Case: Crea un nuovo piano abbonamento
 */
class CreateSubscriptionPlan
{
    public function __construct(
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    /**
     * @param array<string, mixed> $data Dati del piano
     */
    public function execute(Gym $gym, array $data): SubscriptionPlan
    {
        $plan = new SubscriptionPlan();
        $plan->setGym($gym);
        $plan->setName($data['name']);
        $plan->setDescription($data['description'] ?? null);
        $plan->setDuration((int) $data['duration']);
        $plan->setPrice((float) $data['price']);
        $plan->setIncludePT($data['include_pt'] ?? false);
        $plan->setPtSessionsIncluded($data['pt_sessions_included'] ?? null);
        $plan->setMaxAccessPerWeek($data['max_access_per_week'] ?? null);
        $plan->setFeatures($data['features'] ?? []);
        $plan->setIsActive(true);

        $this->planRepository->save($plan, true);

        return $plan;
    }
}
