<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Membership\Entity\SubscriptionPlan;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;

/**
 * Use Case: Elimina un piano abbonamento
 */
class DeleteSubscriptionPlan
{
    public function __construct(
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    /**
     * @throws \RuntimeException se il piano non può essere eliminato
     */
    public function execute(SubscriptionPlan $plan): void
    {
        // TODO: Verificare se ci sono abbonamenti attivi con questo piano
        // Se sì, non permettere l'eliminazione

        $this->planRepository->remove($plan, true);
    }
}
