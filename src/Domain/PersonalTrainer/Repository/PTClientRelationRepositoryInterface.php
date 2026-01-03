<?php

namespace App\Domain\PersonalTrainer\Repository;

use App\Domain\PersonalTrainer\Entity\PTClientRelation;

/**
 * Repository interface per PTClientRelation
 *
 * Nota: Metodi standard (find, findBy, findOneBy, etc.)
 * sono già forniti da ServiceEntityRepository.
 * Questa interfaccia mantiene il contratto Domain/Infrastructure.
 */
interface PTClientRelationRepositoryInterface
{
    /**
     * Trova una relazione PT-Cliente per criteri specifici
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @return PTClientRelation|null
     */
    public function findOneBy(array $criteria, ?array $orderBy = null);
}
