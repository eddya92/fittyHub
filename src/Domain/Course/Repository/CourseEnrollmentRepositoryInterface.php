<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseSession;
use App\Domain\User\Entity\User;

/**
 * Repository interface per CourseEnrollment (iscrizione a corsi)
 */
interface CourseEnrollmentRepositoryInterface
{
    public function save(CourseEnrollment $enrollment, bool $flush = false): void;

    public function remove(CourseEnrollment $enrollment, bool $flush = false): void;

    /**
     * Trova prenotazione attiva di un utente per uno specifico schedule
     */
    public function findActiveEnrollmentForSchedule(User $user, CourseSchedule $schedule): ?CourseEnrollment;

    /**
     * Trova tutte le prenotazioni attive di un utente
     */
    public function findActiveEnrollmentsByUser(User $user): array;

    /**
     * Conta le prenotazioni attive per uno schedule
     */
    public function countActiveEnrollmentsBySchedule(CourseSchedule $schedule): int;

    /**
     * Trova un enrollment con criteri specifici
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?CourseEnrollment;

    /**
     * Trova iscrizione attiva di un utente per una sessione specifica
     */
    public function findActiveEnrollmentForUserAndSession(User $user, CourseSession $session): ?CourseEnrollment;
}
