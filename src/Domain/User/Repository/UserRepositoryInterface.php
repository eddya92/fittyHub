<?php

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;

/**
 * Repository interface per User
 *
 * Nota: Attualmente usa solo metodi standard (find, findAll, save)
 * che sono già forniti da ServiceEntityRepository.
 * Questa interfaccia mantiene il contratto Domain/Infrastructure.
 */
interface UserRepositoryInterface
{
    // Al momento non ci sono metodi business-specific custom
    // I metodi standard sono ereditati da ServiceEntityRepository
}
