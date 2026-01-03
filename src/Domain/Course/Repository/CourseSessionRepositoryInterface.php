<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Entity\CourseSchedule;

interface CourseSessionRepositoryInterface
{
    public function save(CourseSession $session, bool $flush = false): void;

    public function remove(CourseSession $session, bool $flush = false): void;

    public function flush(): void;

    /**
     * Trova tutte le sessioni tra due date
     */
    public function findBetweenDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array;

    /**
     * Trova sessioni di un corso specifico tra due date
     */
    public function findByCourseAndDateRange(GymCourse $course, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;

    /**
     * Trova sessione specifica di uno schedule in una data
     */
    public function findByScheduleAndDate(CourseSchedule $schedule, \DateTimeInterface $date): ?CourseSession;

    /**
     * Trova tutte le sessioni future (da oggi in poi)
     */
    public function findUpcoming(int $limit = null): array;

    /**
     * Trova tutte le sessioni passate
     */
    public function findPast(int $limit = null): array;

    /**
     * Trova sessioni per stato
     */
    public function findByStatus(string $status): array;

    /**
     * Verifica se esiste già una sessione per schedule e data
     */
    public function exists(CourseSchedule $schedule, \DateTimeInterface $date): bool;

    /**
     * Trova sessioni prossime in una finestra temporale (per promemoria)
     */
    public function findUpcomingSessions(\DateTimeInterface $startWindow, \DateTimeInterface $endWindow): array;
}