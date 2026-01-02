<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;

/**
 * Use Case: Aggiorna un piano abbonamento
 */
class UpdateSubscriptionPlan
{
    public function __construct(
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    /**
     * @param array<string, mixed> $data Dati aggiornati
     */
    public function execute(SubscriptionPlan $plan, Gym $gym, array $data): void
    {
        $plan->setGym($gym);
        $plan->setName($data['name']);
        $plan->setDescription($data['description'] ?? null);
        $plan->setDuration((int) $data['duration']);
        $plan->setPrice((float) $data['price']);
        $plan->setIncludePT($data['include_pt'] ?? false);
        $plan->setPtSessionsIncluded($data['pt_sessions_included'] ?? null);
        $plan->setMaxAccessPerWeek($data['max_access_per_week'] ?? null);
        $plan->setFeatures($data['features'] ?? []);

        $this->planRepository->save($plan, true);
    }
}
