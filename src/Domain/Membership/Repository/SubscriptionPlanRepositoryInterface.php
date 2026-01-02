<?php

namespace App\Domain\Membership\Repository;

use App\Domain\Membership\Entity\SubscriptionPlan;

/**
 * Repository interface per SubscriptionPlan
 *
 * Nota: Attualmente usa solo metodi standard (find, findAll, findBy, save, remove)
 * che sono già forniti da ServiceEntityRepository.
 * Questa interfaccia mantiene il contratto Domain/Infrastructure.
 */
interface SubscriptionPlanRepositoryInterface
{
    // Al momento non ci sono metodi business-specific custom
    // I metodi standard sono ereditati da ServiceEntityRepository
}
