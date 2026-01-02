<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseSchedule;

/**
 * Repository interface per CourseSchedule
 *
 * Nota: Attualmente usa solo metodi standard (find, save, remove)
 * che sono già forniti da ServiceEntityRepository.
 * Questa interfaccia mantiene il contratto Domain/Infrastructure.
 */
interface CourseScheduleRepositoryInterface
{
    // Al momento non ci sono metodi business-specific custom
    // I metodi standard sono ereditati da ServiceEntityRepository
}
