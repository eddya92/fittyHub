<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\User\Entity\User;

/**
 * Use Case: Prenota uno slot (orario) di un corso
 */
class BookCourseSlot
{
    public function __construct(
        private CourseEnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * Prenota un posto per un utente in uno specifico orario del corso
     *
     * @throws \RuntimeException
     */
    public function execute(User $user, CourseSchedule $schedule): CourseEnrollment
    {
        // 1. Verifica che l'utente non sia già iscritto a questo orario
        $existingEnrollment = $this->enrollmentRepository->findActiveEnrollmentForSchedule($user, $schedule);

        if ($existingEnrollment) {
            throw new \RuntimeException('Sei già iscritto a questo orario del corso.');
        }

        // 2. Verifica che ci siano posti disponibili
        if (!$schedule->hasAvailableSpots()) {
            throw new \RuntimeException('Non ci sono più posti disponibili per questo orario. Prova ad entrare in lista d\'attesa.');
        }

        // 3. Verifica che il corso sia attivo
        if ($schedule->getCourse()->getStatus() !== 'active') {
            throw new \RuntimeException('Questo corso non è più attivo.');
        }

        // 4. Crea la prenotazione
        $enrollment = new CourseEnrollment();
        $enrollment->setCourse($schedule->getCourse());
        $enrollment->setSchedule($schedule);
        $enrollment->setUser($user);
        $enrollment->setStatus('active');

        $this->enrollmentRepository->save($enrollment, true);

        return $enrollment;
    }
}