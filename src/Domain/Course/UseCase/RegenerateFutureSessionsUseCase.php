<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;

/**
 * Use Case: Rigenera le sessioni future
 *
 * Elimina tutte le sessioni future programmate e le ricrea
 * basandosi sull'ultima configurazione degli orari.
 *
 * Usato dal cron mensile per aggiornare il calendario.
 */
class RegenerateFutureSessionsUseCase
{
    public function __construct(
        private CourseSessionRepositoryInterface $sessionRepository,
        private CourseScheduleRepositoryInterface $scheduleRepository,
        private GenerateCourseSessionsUseCase $generateSessions
    ) {}

    /**
     * Rigenera le sessioni future per le prossime N settimane
     *
     * @param int $weeks Numero di settimane da generare
     * @param bool $deleteFromToday Se true elimina da oggi, altrimenti da inizio settimana prossima
     * @return array ['deleted' => int, 'created' => int]
     */
    public function execute(int $weeks = 5, bool $deleteFromToday = true): array
    {
        $startDate = $deleteFromToday
            ? new \DateTime('today')
            : new \DateTime('next monday');

        // 1. Elimina tutte le sessioni future con status "scheduled"
        $deletedCount = $this->deleteFutureSessions($startDate);

        // 2. Rigenera le sessioni per le prossime N settimane
        $createdCount = $this->generateSessions->execute($weeks, true);

        return [
            'deleted' => $deletedCount,
            'created' => $createdCount,
        ];
    }

    /**
     * Elimina tutte le sessioni programmate dalla data specificata in poi
     */
    private function deleteFutureSessions(\DateTime $fromDate): int
    {
        // Trova tutte le sessioni future
        $endDate = (clone $fromDate)->modify('+1 year'); // Limite massimo
        $sessions = $this->sessionRepository->findBetweenDates($fromDate, $endDate);

        $deletedCount = 0;
        foreach ($sessions as $session) {
            // Elimina solo sessioni "scheduled" (non quelle completate o cancellate)
            if ($session->getStatus() === 'scheduled') {
                $this->sessionRepository->remove($session);
                $deletedCount++;
            }
        }

        // Flush tutte le eliminazioni
        if ($deletedCount > 0) {
            $this->sessionRepository->flush();
        }

        return $deletedCount;
    }

    /**
     * Rigenera sessioni per il mese corrente + prossimo
     * (circa 8 settimane dall'inizio del mese corrente)
     */
    public function regenerateCurrentAndNextMonth(): array
    {
        // Elimina da oggi in poi
        $deletedCount = $this->deleteFutureSessions(new \DateTime('today'));

        // Genera per 8 settimane dall'inizio mese corrente
        $startOfMonth = new \DateTime('first day of this month');
        $createdCount = $this->generateSessions->execute(8, true);

        return [
            'deleted' => $deletedCount,
            'created' => $createdCount,
        ];
    }
}
