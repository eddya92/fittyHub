<?php

namespace App\Domain\Invitation\Repository;

use App\Domain\Invitation\Entity\GymPTInvitation;

/**
 * Repository interface per GymPTInvitation
 *
 * Nota: Metodi standard (find, findBy, save, remove, count, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface InvitationRepositoryInterface
{
    /**
     * Trova inviti con filtri custom
     */
    public function findWithFilters(?string $status, ?string $search): array;
}
