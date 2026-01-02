<?php

namespace App\Domain\Membership\Service;

use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\SubscriptionPlanRepository;
use App\Domain\Gym\Entity\Gym;

/**
 * Service per la gestione dei piani abbonamento
 */
class SubscriptionPlanService
{
    public function __construct(
        private SubscriptionPlanRepository $planRepository
    ) {}

    /**
     * Crea un nuovo piano abbonamento
     */
    public function createPlan(Gym $gym, array $data): SubscriptionPlan
    {
        $plan = new SubscriptionPlan();
        $this->updatePlanData($plan, $gym, $data);
        $plan->setIsActive(true);

        $this->planRepository->save($plan, true);

        return $plan;
    }

    /**
     * Aggiorna un piano abbonamento
     */
    public function updatePlan(SubscriptionPlan $plan, Gym $gym, array $data): void
    {
        $this->updatePlanData($plan, $gym, $data);
        $plan->setUpdatedAt(new \DateTimeImmutable());

        $this->planRepository->save($plan, true);
    }

    /**
     * Attiva/disattiva un piano abbonamento
     */
    public function togglePlan(SubscriptionPlan $plan): void
    {
        $plan->setIsActive(!$plan->isActive());
        $plan->setUpdatedAt(new \DateTimeImmutable());

        $this->planRepository->save($plan, true);
    }

    /**
     * Elimina un piano abbonamento
     */
    public function deletePlan(SubscriptionPlan $plan): void
    {
        // TODO: Verificare se il piano è in uso prima di eliminare
        $this->planRepository->remove($plan, true);
    }

    /**
     * Helper per aggiornare i dati di un piano
     */
    private function updatePlanData(SubscriptionPlan $plan, Gym $gym, array $data): void
    {
        $getIntOrNull = function(string $key) use ($data): ?int {
            $value = $data[$key] ?? null;
            if ($value === null || $value === '') {
                return null;
            }
            return is_numeric($value) ? (int)$value : null;
        };

        $plan->setGym($gym);
        $plan->setName($data['name']);
        $plan->setDescription($data['description'] ?? null);
        $plan->setDuration((int)($data['duration'] ?? 1));
        $plan->setPrice($data['price']);
        $plan->setIncludePT(($data['include_pt'] ?? '0') === '1');
        $plan->setPtSessionsIncluded($getIntOrNull('pt_sessions_included'));
        $plan->setMaxAccessPerWeek($getIntOrNull('max_access_per_week'));

        $features = array_filter($data['features'] ?? []);
        $plan->setFeatures($features);
    }
}
