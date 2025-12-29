<?php

namespace App\Application\Service;

use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Repository\GymCourseRepository;
use App\Domain\Course\Repository\CourseScheduleRepository;
use App\Domain\Course\Repository\CourseCategoryRepository;
use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use App\Domain\Gym\Entity\Gym;

class CourseService
{
    public function __construct(
        private GymCourseRepository $courseRepository,
        private CourseScheduleRepository $scheduleRepository,
        private CourseCategoryRepository $categoryRepository,
        private PersonalTrainerRepository $trainerRepository
    ) {}

    /**
     * Crea un nuovo corso
     */
    public function createCourse(array $data, Gym $gym): GymCourse
    {
        $course = new GymCourse();
        $course->setName($data['name']);
        $course->setDescription($data['description'] ?? null);
        $course->setMaxParticipants((int)$data['max_participants']);
        $course->setStatus($data['status'] ?? 'active');
        $course->setGym($gym);

        // Categoria
        if (!empty($data['category_id'])) {
            $category = $this->categoryRepository->find($data['category_id']);
            if ($category) {
                $course->setCategory($category);
            }
        }

        // Istruttore
        if (!empty($data['instructor_id'])) {
            $instructor = $this->trainerRepository->find($data['instructor_id']);
            if ($instructor) {
                $course->setInstructor($instructor);
            }
        }

        $this->courseRepository->save($course, true);

        return $course;
    }

    /**
     * Aggiorna un corso esistente
     */
    public function updateCourse(GymCourse $course, array $data): GymCourse
    {
        $course->setName($data['name']);
        $course->setDescription($data['description'] ?? null);
        $course->setMaxParticipants((int)$data['max_participants']);
        $course->setStatus($data['status']);

        // Categoria
        if (!empty($data['category_id'])) {
            $category = $this->categoryRepository->find($data['category_id']);
            if ($category) {
                $course->setCategory($category);
            }
        }

        // Istruttore
        if (!empty($data['instructor_id'])) {
            $instructor = $this->trainerRepository->find($data['instructor_id']);
            $course->setInstructor($instructor);
        } else {
            $course->setInstructor(null);
        }

        $this->courseRepository->save($course, true);

        return $course;
    }

    /**
     * Aggiunge un orario a un corso
     */
    public function addSchedule(GymCourse $course, array $data): CourseSchedule
    {
        $schedule = new CourseSchedule();
        $schedule->setCourse($course);
        $schedule->setDayOfWeek($data['day_of_week']);
        $schedule->setStartTime(new \DateTime($data['start_time']));
        $schedule->setEndTime(new \DateTime($data['end_time']));

        $this->scheduleRepository->save($schedule, true);

        return $schedule;
    }

    /**
     * Rimuove un orario
     */
    public function deleteSchedule(CourseSchedule $schedule): void
    {
        $this->scheduleRepository->remove($schedule, true);
    }

    /**
     * Ottiene tutti i trainer disponibili
     */
    public function getAvailableTrainers(): array
    {
        return $this->trainerRepository->findAll();
    }

    /**
     * Ottiene tutte le categorie
     */
    public function getCategories(): array
    {
        return $this->categoryRepository->findBy([], ['name' => 'ASC']);
    }
}
