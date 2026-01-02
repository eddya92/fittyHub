<?php

namespace App\Domain\Membership\Repository;

use App\Domain\Membership\Entity\Enrollment;
use App\Domain\User\Entity\User;
use App\Domain\Gym\Entity\Gym;

/**
 * Repository interface per Enrollment (quota iscrizione)
 *
 * Nota: Metodi standard (find, findBy, save, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface EnrollmentRepositoryInterface
{
    /**
     * Trova quota iscrizione attiva per un utente in una palestra
     */
    public function findActiveEnrollment(User $user, Gym $gym): ?Enrollment;

    /**
     * Trova quote iscrizione in scadenza nei prossimi N giorni
     */
    public function findExpiringEnrollments(int $days): array;

    /**
     * Ottiene lo storico quote iscrizione per un utente
     */
    public function findByUser(User $user): array;
}
