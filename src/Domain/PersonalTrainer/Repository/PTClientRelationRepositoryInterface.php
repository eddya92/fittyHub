<?php

namespace App\Domain\PersonalTrainer\Repository;

use App\Domain\PersonalTrainer\Entity\PTClientRelation;

/**
 * Repository interface per PTClientRelation
 *
 * Nota: Attualmente usa solo metodi standard (find, findBy)
 * che sono già forniti da ServiceEntityRepository.
 * Questa interfaccia mantiene il contratto Domain/Infrastructure.
 */
interface PTClientRelationRepositoryInterface
{
    // Al momento non ci sono metodi business-specific custom
    // I metodi standard sono ereditati da ServiceEntityRepository
}
