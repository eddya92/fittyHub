<?php

namespace App\Domain\Membership\Service;

use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\User\Entity\User;

class EnrollmentService
{
    public function __construct(
        private CourseEnrollmentRepositoryInterface $enrollmentRepository
    ) {}

    /**
     * Iscrive un utente a uno specifico orario di un corso
     *
     * @throws \RuntimeException se non ci sono posti disponibili o l'utente è già iscritto
     */
    public function enrollUser(GymCourse $course, CourseSchedule $schedule, User $user): CourseEnrollment
    {
        // Verifica schedule appartiene al corso
        if ($schedule->getCourse()->getId() !== $course->getId()) {
            throw new \RuntimeException('Orario non valido per questo corso');
        }

        // Verifica posti disponibili
        if (!$schedule->hasAvailableSpots()) {
            throw new \RuntimeException('Orario al completo');
        }

        // Verifica se già iscritto a QUESTO orario
        $existing = $this->enrollmentRepository->findOneBy([
            'schedule' => $schedule,
            'user' => $user,
            'status' => 'active'
        ]);

        if ($existing) {
            throw new \RuntimeException('Utente già iscritto a questo orario');
        }

        $enrollment = new CourseEnrollment();
        $enrollment->setCourse($course);
        $enrollment->setSchedule($schedule);
        $enrollment->setUser($user);
        $enrollment->setStatus('active');

        $this->enrollmentRepository->save($enrollment, true);

        return $enrollment;
    }

    /**
     * Cancella un'iscrizione
     */
    public function cancelEnrollment(CourseEnrollment $enrollment): void
    {
        $enrollment->setStatus('cancelled');
        $enrollment->setCancelledAt(new \DateTimeImmutable());

        $this->enrollmentRepository->save($enrollment, true);
    }

    /**
     * Ottiene le iscrizioni attive per un corso
     */
    public function getActiveEnrollments(GymCourse $course): array
    {
        return $this->enrollmentRepository->findBy([
            'course' => $course,
            'status' => 'active'
        ]);
    }

    /**
     * Verifica se un utente è iscritto a uno specifico orario
     */
    public function isUserEnrolled(CourseSchedule $schedule, User $user): bool
    {
        $enrollment = $this->enrollmentRepository->findOneBy([
            'schedule' => $schedule,
            'user' => $user,
            'status' => 'active'
        ]);

        return $enrollment !== null;
    }

    /**
     * Iscrive un utente a una sessione specifica
     *
     * @throws \RuntimeException se non ci sono posti disponibili o l'utente è già iscritto
     */
    public function enrollUserToSession(CourseSession $session, User $user): CourseEnrollment
    {
        // Verifica che la sessione sia programmata (non passata o cancellata)
        if ($session->getStatus() !== 'scheduled') {
            throw new \RuntimeException('Non è possibile iscriversi a questa sessione');
        }

        // Verifica che la sessione non sia passata
        if ($session->isInPast()) {
            throw new \RuntimeException('Non è possibile iscriversi a una sessione passata');
        }

        // Verifica posti disponibili
        if (!$session->hasAvailableSpots()) {
            throw new \RuntimeException('Sessione al completo');
        }

        // Verifica se già iscritto a QUESTA sessione
        $existing = $this->enrollmentRepository->findOneBy([
            'session' => $session,
            'user' => $user,
            'status' => 'active'
        ]);

        if ($existing) {
            throw new \RuntimeException('Utente già iscritto a questa sessione');
        }

        $enrollment = new CourseEnrollment();
        $enrollment->setCourse($session->getCourse());
        $enrollment->setSession($session);
        $enrollment->setUser($user);
        $enrollment->setStatus('active');

        $this->enrollmentRepository->save($enrollment, true);

        return $enrollment;
    }
}
