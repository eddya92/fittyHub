<?php

namespace App\Domain\Course\Service;

use App\Domain\Course\Repository\CourseRepositoryInterface;
use App\Domain\Course\Repository\CourseCategoryRepositoryInterface;
use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymSettings;
use App\Domain\Gym\Repository\GymSettingsRepositoryInterface;

class CourseCalendarService
{
    private const WEEK_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private CourseCategoryRepositoryInterface $categoryRepository,
        private GymSettingsRepositoryInterface $settingsRepository,
        private CourseSessionRepositoryInterface $sessionRepository
    ) {}

    /**
     * Genera il calendario settimanale con tutti i corsi organizzati per giorno e ora
     */
    public function getWeeklyCalendar(): array
    {
        $courses = $this->courseRepository->findActiveWithSchedules();

        // Inizializza calendario vuoto
        $calendar = [];
        foreach (self::WEEK_DAYS as $day) {
            $calendar[$day] = [];
        }

        // Popola calendario
        foreach ($courses as $course) {
            foreach ($course->getSchedules() as $schedule) {
                $day = $schedule->getDayOfWeek();
                if (isset($calendar[$day])) {
                    $calendar[$day][] = [
                        'course' => $course,
                        'schedule' => $schedule
                    ];
                }
            }
        }

        // Ordina per ora di inizio
        foreach ($calendar as $day => $sessions) {
            usort($calendar[$day], function($a, $b) {
                return $a['schedule']->getStartTime() <=> $b['schedule']->getStartTime();
            });
        }

        return $calendar;
    }

    /**
     * Ottiene tutte le categorie per la legenda
     */
    public function getCategories(): array
    {
        return $this->categoryRepository->findBy([], ['name' => 'ASC']);
    }

    /**
     * Ottiene o crea le impostazioni orari per una palestra
     */
    public function getOrCreateSettings(Gym $gym): GymSettings
    {
        $settings = $this->settingsRepository->findOneBy(['gym' => $gym]);

        if (!$settings) {
            $settings = new GymSettings();
            $settings->setGym($gym);
            $this->settingsRepository->save($settings, true);
        }

        return $settings;
    }

    /**
     * Ottiene gli slot orari configurati
     */
    public function getTimeSlots(?Gym $gym = null): array
    {
        if (!$gym) {
            return [];
        }

        $settings = $this->getOrCreateSettings($gym);
        return $settings->getTimeSlots();
    }

    /**
     * Genera calendario con sessioni specifiche per una settimana
     *
     * @param \DateTime|null $weekStart Inizio settimana (Lunedì). Se null, usa settimana corrente
     * @return array Calendario organizzato per giorno con sessioni specifiche
     */
    public function getWeeklyCalendarWithSessions(?\DateTime $weekStart = null): array
    {
        if ($weekStart === null) {
            $weekStart = new \DateTime('monday this week');
        } else {
            // Assicurati che sia un lunedì
            $weekStart = clone $weekStart;
            $weekStart->modify('monday this week');
        }

        $weekEnd = (clone $weekStart)->modify('+6 days');

        // Ottieni tutte le sessioni della settimana
        $sessions = $this->sessionRepository->findBetweenDates($weekStart, $weekEnd);

        // Inizializza calendario vuoto
        $calendar = [];
        foreach (self::WEEK_DAYS as $day) {
            $calendar[$day] = [];
        }

        // Mappa giorno numero -> nome
        $dayNumberToName = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        // Popola calendario con sessioni
        foreach ($sessions as $session) {
            $sessionDate = $session->getSessionDate();
            $dayNumber = (int)$sessionDate->format('N');
            $dayName = $dayNumberToName[$dayNumber] ?? null;

            if ($dayName && isset($calendar[$dayName])) {
                $calendar[$dayName][] = $session;
            }
        }

        // Ordina per ora di inizio
        foreach ($calendar as $day => $daySessions) {
            usort($calendar[$day], function($a, $b) {
                return $a->getSchedule()->getStartTime() <=> $b->getSchedule()->getStartTime();
            });
        }

        return $calendar;
    }

    /**
     * Ottiene informazioni sulla settimana corrente
     */
    public function getCurrentWeekInfo(): array
    {
        $monday = new \DateTime('monday this week');
        $sunday = (clone $monday)->modify('+6 days');

        return [
            'start' => $monday,
            'end' => $sunday,
            'label' => $this->getWeekLabel($monday),
        ];
    }

    /**
     * Ottiene informazioni su una settimana specifica
     */
    public function getWeekInfo(\DateTime $date): array
    {
        $monday = clone $date;
        $monday->modify('monday this week');
        $sunday = (clone $monday)->modify('+6 days');

        return [
            'start' => $monday,
            'end' => $sunday,
            'label' => $this->getWeekLabel($monday),
        ];
    }

    /**
     * Genera label leggibile per una settimana
     */
    private function getWeekLabel(\DateTime $monday): string
    {
        $sunday = (clone $monday)->modify('+6 days');

        $monthStart = (int)$monday->format('n');
        $monthEnd = (int)$sunday->format('n');

        if ($monthStart === $monthEnd) {
            return sprintf(
                '%d - %d %s %s',
                (int)$monday->format('d'),
                (int)$sunday->format('d'),
                $this->getItalianMonth($monthStart),
                $monday->format('Y')
            );
        } else {
            return sprintf(
                '%d %s - %d %s %s',
                (int)$monday->format('d'),
                $this->getItalianMonth($monthStart),
                (int)$sunday->format('d'),
                $this->getItalianMonth($monthEnd),
                $monday->format('Y')
            );
        }
    }

    /**
     * Ottiene nome mese in italiano
     */
    private function getItalianMonth(int $month): string
    {
        $months = [
            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
            5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
            9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
        ];

        return $months[$month] ?? '';
    }

    /**
     * Calcola settimana precedente
     */
    public function getPreviousWeek(\DateTime $currentWeek): \DateTime
    {
        $previous = clone $currentWeek;
        return $previous->modify('-7 days');
    }

    /**
     * Calcola settimana successiva
     */
    public function getNextWeek(\DateTime $currentWeek): \DateTime
    {
        $next = clone $currentWeek;
        return $next->modify('+7 days');
    }
}
