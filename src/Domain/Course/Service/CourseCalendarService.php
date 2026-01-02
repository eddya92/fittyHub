<?php

namespace App\Domain\Course\Service;

use App\Domain\Course\Repository\CourseRepositoryInterface;
use App\Domain\Course\Repository\CourseCategoryRepositoryInterface;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymSettings;
use App\Domain\Gym\Repository\GymSettingsRepository;

class CourseCalendarService
{
    private const WEEK_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private CourseCategoryRepositoryInterface $categoryRepository,
        private GymSettingsRepository $settingsRepository
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
}
