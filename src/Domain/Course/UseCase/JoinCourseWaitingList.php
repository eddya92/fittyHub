<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseWaitingList;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\Course\Repository\CourseWaitingListRepositoryInterface;
use App\Domain\User\Entity\User;

/**
 * Use Case: Entra in lista d'attesa per un corso pieno
 */
class JoinCourseWaitingList
{
    public function __construct(
        private CourseWaitingListRepositoryInterface $waitingListRepository,
        private CourseEnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * Aggiunge un utente alla lista d'attesa per uno specifico orario del corso
     *
     * @throws \RuntimeException
     */
    public function execute(User $user, CourseSchedule $schedule): CourseWaitingList
    {
        // 1. Verifica che l'utente non sia già iscritto
        $existingEnrollment = $this->enrollmentRepository->findActiveEnrollmentForSchedule($user, $schedule);

        if ($existingEnrollment) {
            throw new \RuntimeException('Sei già iscritto a questo orario del corso.');
        }

        // 2. Verifica che l'utente non sia già in lista d'attesa
        $existingWaiting = $this->waitingListRepository->findActiveWaitingForSchedule($user, $schedule);

        if ($existingWaiting) {
            throw new \RuntimeException('Sei già in lista d\'attesa per questo orario. Posizione: ' . $existingWaiting->getPosition());
        }

        // 3. Verifica che ci siano davvero posti esauriti
        if ($schedule->hasAvailableSpots()) {
            throw new \RuntimeException('Ci sono ancora posti disponibili! Puoi prenotare direttamente.');
        }

        // 4. Crea l'entrata in lista d'attesa
        $waitingList = new CourseWaitingList();
        $waitingList->setSchedule($schedule);
        $waitingList->setUser($user);
        $waitingList->setStatus('waiting');

        // Calcola la posizione nella lista
        $nextPosition = $this->waitingListRepository->getNextPositionForSchedule($schedule);
        $waitingList->setPosition($nextPosition);

        $this->waitingListRepository->save($waitingList, true);

        return $waitingList;
    }
}