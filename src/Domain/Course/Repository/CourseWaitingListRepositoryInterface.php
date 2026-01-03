<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseWaitingList;
use App\Domain\User\Entity\User;

interface CourseWaitingListRepositoryInterface
{
    public function save(CourseWaitingList $waitingList, bool $flush = false): void;

    public function remove(CourseWaitingList $waitingList, bool $flush = false): void;

    /**
     * Trova posizione in lista d'attesa per un utente e schedule
     */
    public function findActiveWaitingForSchedule(User $user, CourseSchedule $schedule): ?CourseWaitingList;

    /**
     * Trova tutte le persone in lista d'attesa per uno schedule, ordinate per posizione
     */
    public function findWaitingBySchedule(CourseSchedule $schedule): array;

    /**
     * Conta quante persone sono in lista d'attesa per uno schedule
     */
    public function countWaitingBySchedule(CourseSchedule $schedule): int;

    /**
     * Trova la prossima posizione disponibile per uno schedule
     */
    public function getNextPositionForSchedule(CourseSchedule $schedule): int;
}