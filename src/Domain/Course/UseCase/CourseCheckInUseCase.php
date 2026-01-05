<?php

namespace App\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\Gym\Service\CheckInService;
use App\Domain\User\Entity\User;

class CourseCheckInUseCase
{
    public function __construct(
        private CheckInService $checkInService,
        private CourseEnrollmentRepositoryInterface $enrollmentRepository,
        private GymAttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * Verifica se l'utente può fare check-in per una sessione corso
     *
     * @return array ['allowed' => bool, 'reason' => string|null, 'enrollment' => CourseEnrollment|null]
     */
    public function canCheckInToCourse(User $user, CourseSession $session): array
    {
        // 1. Verifica che la sessione sia oggi
        if (!$session->isToday()) {
            return [
                'allowed' => false,
                'reason' => 'Il check-in è disponibile solo il giorno della sessione.'
            ];
        }

        // 2. Verifica che l'utente sia iscritto alla sessione
        $enrollment = $this->enrollmentRepository->findActiveEnrollmentForUserAndSession($user, $session);

        if (!$enrollment) {
            return [
                'allowed' => false,
                'reason' => 'Non sei iscritto a questa sessione del corso.'
            ];
        }

        // 3. Verifica se ha già fatto check-in per questa sessione
        $existingAttendance = $this->attendanceRepository->findByUserAndSession($user, $session);

        if ($existingAttendance) {
            return [
                'allowed' => false,
                'reason' => 'Check-in già effettuato per questa sessione alle ' .
                           $existingAttendance->getCheckInTime()->format('H:i') . '.'
            ];
        }

        // 4. Verifica requisiti base (abbonamento + certificato medico)
        $gym = $session->getCourse()->getGym();
        $baseValidation = $this->checkInService->canCheckIn($user, $gym);

        if (!$baseValidation['allowed']) {
            return $baseValidation;
        }

        return [
            'allowed' => true,
            'reason' => null,
            'enrollment' => $enrollment,
            'membership' => $baseValidation['membership']
        ];
    }

    /**
     * Effettua il check-in per una sessione corso
     */
    public function checkInToCourse(User $user, CourseSession $session): GymAttendance
    {
        $validation = $this->canCheckInToCourse($user, $session);

        if (!$validation['allowed']) {
            throw new \RuntimeException($validation['reason']);
        }

        $gym = $session->getCourse()->getGym();

        $attendance = new GymAttendance();
        $attendance->setUser($user);
        $attendance->setGym($gym);
        $attendance->setGymMembership($validation['membership']);
        $attendance->setType('course');
        $attendance->setCourseSession($session);
        $attendance->setCheckInTime(new \DateTime());

        $this->attendanceRepository->save($attendance, true);

        return $attendance;
    }

    /**
     * Ottiene la lista presenze per una sessione corso
     */
    public function getSessionAttendances(CourseSession $session): array
    {
        return $this->attendanceRepository->findBySession($session);
    }

    /**
     * Ottiene le statistiche di partecipazione per un corso
     */
    public function getCourseAttendanceStats(int $courseId, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $stats = $this->attendanceRepository->getAttendanceStatsByCourse($courseId, $from, $to);

        return [
            'total_sessions' => $stats['total_sessions'] ?? 0,
            'total_attendances' => $stats['total_attendances'] ?? 0,
            'average_attendance_rate' => $stats['average_rate'] ?? 0,
            'unique_participants' => $stats['unique_users'] ?? 0
        ];
    }
}