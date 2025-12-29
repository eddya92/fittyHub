<?php

namespace App\Controller\Admin;

use App\Application\Service\CourseService;
use App\Application\Service\CourseCalendarService;
use App\Application\Service\EnrollmentService;
use App\Application\Service\GymUserService;
use App\Domain\Course\Repository\GymCourseRepository;
use App\Domain\Course\Repository\CourseScheduleRepository;
use App\Domain\Course\Repository\CourseEnrollmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/courses')]
class CourseController extends AbstractController
{
    public function __construct(
        private CourseService $courseService,
        private EnrollmentService $enrollmentService,
        private CourseCalendarService $calendarService,
        private GymUserService $gymUserService,
        private GymCourseRepository $courseRepository,
        private CourseScheduleRepository $scheduleRepository,
        private CourseEnrollmentRepository $enrollmentRepository
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

        $courses = $this->courseRepository->findWithFilters($search, $category, $status);

        $stats = [
            'total' => $this->courseRepository->count([]),
            'active' => $this->courseRepository->countByStatus('active'),
            'suspended' => $this->courseRepository->countByStatus('suspended'),
        ];

        return $this->render('admin/courses/index.html.twig', [
            'courses' => $courses,
            'stats' => $stats,
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
        $course = $this->courseRepository->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        $users = $this->gymUserService->getActiveMembers($course->getGym());

        return $this->render('admin/courses/show.html.twig', [
            'course' => $course,
            'users' => $users,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_course_edit')]
    public function edit(int $id, Request $request): Response
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        if ($request->isMethod('POST')) {
            try {
                $this->courseService->updateCourse($course, $request->request->all());
                $this->addFlash('success', 'Corso aggiornato con successo.');
                return $this->redirectToRoute('admin_course_show', ['id' => $id]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore nell\'aggiornamento: ' . $e->getMessage());
            }
        }

        return $this->render('admin/courses/edit.html.twig', [
            'course' => $course,
            'trainers' => $this->courseService->getAvailableTrainers(),
            'categories' => $this->courseService->getCategories(),
        ]);
    }

    #[Route('/{id}/schedule/add', name: 'admin_course_schedule_add', methods: ['POST'])]
    public function addSchedule(int $id, Request $request): Response
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        try {
            $this->courseService->addSchedule($course, $request->request->all());
            $this->addFlash('success', 'Orario aggiunto al corso.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Errore: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $id]);
    }

    #[Route('/schedule/{scheduleId}/delete', name: 'admin_course_schedule_delete', methods: ['POST'])]
    public function deleteSchedule(int $scheduleId): Response
    {
        $schedule = $this->scheduleRepository->find($scheduleId);

        if (!$schedule) {
            $this->addFlash('error', 'Orario non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        $courseId = $schedule->getCourse()->getId();

        try {
            $this->courseService->deleteSchedule($schedule);
            $this->addFlash('success', 'Orario rimosso.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Errore: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
    }

    #[Route('/{id}/enroll', name: 'admin_course_enroll', methods: ['POST'])]
    public function enroll(int $id, Request $request): Response
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        $scheduleId = $request->request->get('schedule_id');
        $userId = $request->request->get('user_id');

        if (!$scheduleId || !$userId) {
            $this->addFlash('error', 'Seleziona utente e orario.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        $schedule = $this->scheduleRepository->find($scheduleId);
        $users = $this->gymUserService->getActiveMembers($course->getGym());
        $user = array_filter($users, fn($u) => $u->getId() == $userId)[0] ?? null;

        if (!$schedule || !$user) {
            $this->addFlash('error', 'Dati non validi.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        try {
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
        $enrollment = $this->enrollmentRepository->find($enrollmentId);

        if (!$enrollment) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_courses');
        }

        $courseId = $enrollment->getCourse()->getId();

        try {
            $this->enrollmentService->cancelEnrollment($enrollment);
            $this->addFlash('success', 'Iscrizione cancellata.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Errore: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
    }
}
