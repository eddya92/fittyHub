<?php

namespace App\Application\Service;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymSettings;
use App\Domain\Gym\Repository\GymSettingsRepository;

class SettingsService
{
    public function __construct(
        private GymSettingsRepository $settingsRepository
    ) {}

    /**
     * Ottiene o crea le settings per una palestra
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
     * Aggiorna le settings
     */
    public function updateSettings(GymSettings $settings, array $data): void
    {
        if (isset($data['course_schedule_start'])) {
            $settings->setCourseScheduleStart(new \DateTime($data['course_schedule_start']));
        }

        if (isset($data['course_schedule_end'])) {
            $settings->setCourseScheduleEnd(new \DateTime($data['course_schedule_end']));
        }

        if (isset($data['time_slot_duration'])) {
            $settings->setTimeSlotDuration((int)$data['time_slot_duration']);
        }

        // Check-in tornello: se il checkbox non è presente, il valore è false
        $settings->setEnableGymCheckIn(isset($data['enable_gym_check_in']) && $data['enable_gym_check_in'] === '1');

        $this->settingsRepository->save($settings, true);
    }
}
