<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\Course\Repository\CourseWaitingListRepositoryInterface;

/**
 * Use Case: Cancella una prenotazione di un corso
 */
class CancelCourseBooking
{
    public function __construct(
        private CourseEnrollmentRepositoryInterface $enrollmentRepository,
        private CourseWaitingListRepositoryInterface $waitingListRepository
    ) {}

    /**
     * Cancella la prenotazione di un corso
     *
     * @throws \RuntimeException
     */
    public function execute(CourseEnrollment $enrollment): void
    {
        // 1. Verifica che la prenotazione sia attiva
        if ($enrollment->getStatus() !== 'active') {
            throw new \RuntimeException('Questa prenotazione è già stata cancellata o completata.');
        }

        // 2. Marca come cancellata
        $enrollment->setStatus('cancelled');
        $enrollment->setCancelledAt(new \DateTimeImmutable());

        $this->enrollmentRepository->save($enrollment, true);

        // 3. Se c'è una lista d'attesa, notifica il primo in lista
        $waitingList = $this->waitingListRepository->findWaitingBySchedule($enrollment->getSchedule());

        if (count($waitingList) > 0) {
            $firstWaiting = $waitingList[0];
            $firstWaiting->markAsNotified();
            $this->waitingListRepository->save($firstWaiting, true);

            // TODO: Inviare notifica push/email al primo in lista d'attesa
            // Questo sarà implementato quando aggiungeremo il sistema di notifiche
        }
    }
}