<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Entity\GymCourse;

/**
 * Repository interface per CourseEnrollment (iscrizione a corsi)
 *
 * Nota: Attualmente usa solo metodi standard (find, findBy, save, remove)
 * che sono già forniti da ServiceEntityRepository.
 * Questa interfaccia mantiene il contratto Domain/Infrastructure.
 */
interface CourseEnrollmentRepositoryInterface
{
    // Al momento non ci sono metodi business-specific custom
    // I metodi standard sono ereditati da ServiceEntityRepository
}
