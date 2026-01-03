<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;
use App\Domain\Course\Repository\CourseSessionRepositoryInterface;

/**
 * Use Case: Genera automaticamente le sessioni future per tutti i corsi attivi
 *
 * Questo use case crea sessioni specifiche (es. "Zumba del Lunedì 6 Gennaio 2026")
 * basandosi sugli orari settimanali configurati (CourseSchedule)
 */
class GenerateCourseSessionsUseCase
{
    private const DAY_MAP = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    ];

    public function __construct(
        private CourseScheduleRepositoryInterface $scheduleRepository,
        private CourseSessionRepositoryInterface $sessionRepository
    ) {}

    /**
     * Genera sessioni per le prossime N settimane
     *
     * @param int $weeks Numero di settimane future per cui generare sessioni
     * @param bool $includeCurrentWeek Se true, parte da lunedì della settimana corrente, altrimenti da oggi
     * @return int Numero di sessioni create
     */
    public function execute(int $weeks = 4, bool $includeCurrentWeek = true): int
    {
        $schedules = $this->scheduleRepository->findAll();
        $sessionsCreated = 0;

        // Parte da inizio settimana corrente o da oggi
        $startDate = $includeCurrentWeek
            ? new \DateTime('monday this week')
            : new \DateTime('today');

        $endDate = (clone $startDate)->modify("+{$weeks} weeks");

        foreach ($schedules as $schedule) {
            // Verifica che il corso sia attivo
            if ($schedule->getCourse()->getStatus() !== 'active') {
                continue;
            }

            $sessionsCreated += $this->generateSessionsForSchedule($schedule, $startDate, $endDate);
        }

        // Flush tutte le sessioni create
        if ($sessionsCreated > 0) {
            $this->sessionRepository->flush();
        }

        return $sessionsCreated;
    }

    /**
     * Genera sessioni per uno specifico schedule tra due date
     */
    private function generateSessionsForSchedule(
        CourseSchedule $schedule,
        \DateTime $startDate,
        \DateTime $endDate
    ): int {
        $sessionsCreated = 0;
        $dayOfWeek = $schedule->getDayOfWeek();

        if (!isset(self::DAY_MAP[$dayOfWeek])) {
            return 0;
        }

        $targetDayNumber = self::DAY_MAP[$dayOfWeek];

        // Trova il primo giorno target a partire da startDate
        $currentDate = clone $startDate;
        $currentDayNumber = (int)$currentDate->format('N');

        // Calcola giorni da aggiungere per arrivare al giorno target
        if ($currentDayNumber <= $targetDayNumber) {
            $daysToAdd = $targetDayNumber - $currentDayNumber;
        } else {
            $daysToAdd = 7 - ($currentDayNumber - $targetDayNumber);
        }

        $currentDate->modify("+{$daysToAdd} days");

        // Genera sessioni settimanali fino a endDate
        while ($currentDate <= $endDate) {
            // Verifica se la sessione esiste già
            if (!$this->sessionRepository->exists($schedule, $currentDate)) {
                $session = new CourseSession();
                $session->setCourse($schedule->getCourse());
                $session->setSchedule($schedule);
                $session->setSessionDate(clone $currentDate);
                $session->setStatus('scheduled');
                // maxParticipants viene ereditato dal corso tramite getMaxParticipants()

                $this->sessionRepository->save($session);
                $sessionsCreated++;
            }

            // Vai alla settimana successiva
            $currentDate->modify('+7 days');
        }

        return $sessionsCreated;
    }

    /**
     * Genera sessioni per una settimana specifica
     *
     * @param \DateTime $weekStart Inizio della settimana (Lunedì)
     * @return int Numero di sessioni create
     */
    public function generateForWeek(\DateTime $weekStart): int
    {
        $weekEnd = (clone $weekStart)->modify('+6 days');
        return $this->execute(1); // Riutilizza la logica esistente
    }

    /**
     * Genera sessioni per un singolo schedule e una settimana specifica
     */
    public function generateForScheduleAndWeek(CourseSchedule $schedule, \DateTime $weekStart): ?CourseSession
    {
        $weekEnd = (clone $weekStart)->modify('+6 days');
        $this->generateSessionsForSchedule($schedule, $weekStart, $weekEnd);

        // Trova la sessione appena creata per questa settimana
        $dayOfWeek = $schedule->getDayOfWeek();
        $targetDayNumber = self::DAY_MAP[$dayOfWeek] ?? 1;

        $sessionDate = clone $weekStart;
        $currentDayNumber = (int)$sessionDate->format('N');

        if ($currentDayNumber <= $targetDayNumber) {
            $daysToAdd = $targetDayNumber - $currentDayNumber;
        } else {
            $daysToAdd = 7 - ($currentDayNumber - $targetDayNumber);
        }

        $sessionDate->modify("+{$daysToAdd} days");

        return $this->sessionRepository->findByScheduleAndDate($schedule, $sessionDate);
    }
}