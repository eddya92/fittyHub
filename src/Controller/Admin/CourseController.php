<?php

namespace App\Controller\Admin;

use App\Domain\Course\UseCase\GetCourseById;
use App\Domain\Course\UseCase\SearchCourses;
use App\Domain\Course\UseCase\GetCourseStats;
use App\Domain\Course\UseCase\GetScheduleById;
use App\Domain\Course\UseCase\GetEnrollmentById;
use App\Domain\Course\Service\CourseService;
use App\Domain\Course\Service\CourseCalendarService;
use App\Domain\Membership\Service\EnrollmentService;
use App\Domain\User\Service\GymUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/courses')]
class CourseController extends AbstractController
{
    public function __construct(
        private GetCourseById $getCourseById,
        private SearchCourses $searchCourses,
        private GetCourseStats $getCourseStats,
        private GetScheduleById $getScheduleById,
        private GetEnrollmentById $getEnrollmentById,
        private CourseService $courseService,
        private EnrollmentService $enrollmentService,
        private CourseCalendarService $calendarService,
        private GymUserService $gymUserService
    ) {}

    #[Route('/calendar', name: 'admin_courses_calendar')]
    public function calendar(): Response
    {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        return $this->render('admin/courses/calendar.html.twig', [
            'calendar' => $this->calendarService->getWeeklyCalendar(),
            'categories' => $this->calendarService->getCategories(),
            'timeSlots' => $this->calendarService->getTimeSlots($gym),
            'settings' => $gym ? $this->calendarService->getOrCreateSettings($gym) : null,
        ]);
    }

    #[Route('/', name: 'admin_courses')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search');
        $category = $request->query->get('category');
        $status = $request->query->get('status');

        // Use Cases
        return $this->render('admin/courses/index.html.twig', [
            'courses' => $this->searchCourses->execute($search, $category, $status),
            'stats' => $this->getCourseStats->execute(),
            'current_search' => $search,
            'current_category' => $category,
            'current_status' => $status,
        ]);
    }

    #[Route('/create', name: 'admin_course_create')]
    public function create(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $gym = $this->gymUserService->getPrimaryGym($this->getUser());

            if (!$gym) {
                $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
                return $this->redirectToRoute('admin_courses');
            }

            try {
                $this->courseService->createCourse($request->request->all(), $gym);
                $this->addFlash('success', 'Corso creato con successo.');
                return $this->redirectToRoute('admin_courses');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore nella creazione del corso: ' . $e->getMessage());
            }
        }

        return $this->render('admin/courses/create.html.twig', [
            'trainers' => $this->courseService->getAvailableTrainers(),
            'categories' => $this->courseService->getCategories(),
        ]);
    }

    #[Route('/{id}', name: 'admin_course_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        try {
            // Use Case
            $course = $this->getCourseById->execute($id);
            $users = $this->gymUserService->getActiveMembers($course->getGym());

            return $this->render('admin/courses/show.html.twig', [
                'course' => $course,
                'users' => $users,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_courses');
        }
    }

    #[Route('/{id}/edit', name: 'admin_course_edit')]
    public function edit(int $id, Request $request): Response
    {
        try {
            // Use Case
            $course = $this->getCourseById->execute($id);

            if ($request->isMethod('POST')) {
                $this->courseService->updateCourse($course, $request->request->all());
                $this->addFlash('success', 'Corso aggiornato con successo.');
                return $this->redirectToRoute('admin_course_show', ['id' => $id]);
            }

            return $this->render('admin/courses/edit.html.twig', [
                'course' => $course,
                'trainers' => $this->courseService->getAvailableTrainers(),
                'categories' => $this->courseService->getCategories(),
            ]);
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_courses');
        }
    }

    #[Route('/{id}/schedule/add', name: 'admin_course_schedule_add', methods: ['POST'])]
    public function addSchedule(int $id, Request $request): Response
    {
        try {
            // Use Case
            $course = $this->getCourseById->execute($id);
            $this->courseService->addSchedule($course, $request->request->all());
            $this->addFlash('success', 'Orario aggiunto al corso.');
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $id]);
    }

    #[Route('/schedule/{scheduleId}/delete', name: 'admin_course_schedule_delete', methods: ['POST'])]
    public function deleteSchedule(int $scheduleId): Response
    {
        try {
            // Use Case
            $schedule = $this->getScheduleById->execute($scheduleId);
            $courseId = $schedule->getCourse()->getId();

            $this->courseService->deleteSchedule($schedule);
            $this->addFlash('success', 'Orario rimosso.');

            return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_courses');
        }
    }

    #[Route('/{id}/enroll', name: 'admin_course_enroll', methods: ['POST'])]
    public function enroll(int $id, Request $request): Response
    {
        try {
            // Use Cases
            $course = $this->getCourseById->execute($id);
            $scheduleId = $request->request->getInt('schedule_id');
            $userId = $request->request->getInt('user_id');

            if (!$scheduleId || !$userId) {
                throw new \RuntimeException('Seleziona utente e orario.');
            }

            $schedule = $this->getScheduleById->execute($scheduleId);
            $users = $this->gymUserService->getActiveMembers($course->getGym());
            $user = array_filter($users, fn($u) => $u->getId() == $userId)[0] ?? null;

            if (!$user) {
                throw new \RuntimeException('Utente non valido.');
            }

            $this->enrollmentService->enrollUser($course, $schedule, $user);
            $this->addFlash('success', 'Utente iscritto all\'orario selezionato.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $id]);
    }

    #[Route('/enrollment/{enrollmentId}/cancel', name: 'admin_course_enrollment_cancel', methods: ['POST'])]
    public function cancelEnrollment(int $enrollmentId): Response
    {
        try {
            // Use Case
            $enrollment = $this->getEnrollmentById->execute($enrollmentId);
            $courseId = $enrollment->getCourse()->getId();

            $this->enrollmentService->cancelEnrollment($enrollment);
            $this->addFlash('success', 'Iscrizione cancellata.');

            return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_courses');
        }
    }
}
