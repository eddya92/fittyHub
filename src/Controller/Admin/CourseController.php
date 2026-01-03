<?php

namespace App\Controller\Admin;

use App\Domain\Course\Repository\CourseRepositoryInterface;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use App\Domain\Course\Service\CourseService;
use App\Domain\Course\Service\CourseCalendarService;
use App\Domain\Course\UseCase\GenerateCourseSessionsUseCase;
use App\Domain\Membership\Service\EnrollmentService;
use App\Domain\User\Service\GymUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/courses')]
class CourseController extends AbstractController{
	public function __construct(
		private readonly CourseRepositoryInterface           $courseRepository,
		private readonly CourseScheduleRepositoryInterface   $scheduleRepository,
		private readonly CourseEnrollmentRepositoryInterface $enrollmentRepository,
		private readonly CourseSessionRepositoryInterface    $sessionRepository,
		private readonly CourseService                       $courseService,
		private readonly EnrollmentService                   $enrollmentService,
		private readonly CourseCalendarService               $calendarService,
		private readonly GymUserService                      $gymUserService,
		private readonly GenerateCourseSessionsUseCase       $generateSessions
	){
	}

	#[Route('/calendar', name: 'admin_courses_calendar')]
	public function calendar(Request $request) : Response{
		$gym = $this->gymUserService->getPrimaryGym($this->getUser());

		// Ottieni settimana da query string o usa corrente
		$weekParam = $request->query->get('week');
		if ($weekParam) {
			try {
				$weekStart = new \DateTime($weekParam);
			} catch (\Exception $e) {
				$weekStart = new \DateTime('monday this week');
			}
		} else {
			$weekStart = new \DateTime('monday this week');
		}

		// Genera sessioni per le prossime 4 settimane se non esistono
		$this->generateSessions->execute(4);

		$weekInfo = $this->calendarService->getWeekInfo($weekStart);
		$calendar = $this->calendarService->getWeeklyCalendarWithSessions($weekStart);

		$previousWeek = $this->calendarService->getPreviousWeek($weekStart);
		$nextWeek = $this->calendarService->getNextWeek($weekStart);

		return $this->render('admin/courses/calendar.html.twig', [
			'calendar'     => $calendar,
			'categories'   => $this->calendarService->getCategories(),
			'timeSlots'    => $this->calendarService->getTimeSlots($gym),
			'settings'     => $gym ? $this->calendarService->getOrCreateSettings($gym) : null,
			'weekInfo'     => $weekInfo,
			'weekStart'    => $weekStart,
			'previousWeek' => $previousWeek,
			'nextWeek'     => $nextWeek,
		]);
	}

	#[Route('/', name: 'admin_courses')]
	public function index(Request $request) : Response{
		$search = $request->query->get('search');
		$category = $request->query->get('category');
		$status = $request->query->get('status');

		$gym = $this->gymUserService->getPrimaryGym($this->getUser());

		// Usa direttamente il repository
		$courses = $this->courseRepository->findAll(); // TODO: implementare filtri se necessari

		$stats = [
			'total' => count($courses),
			'active' => count(array_filter($courses, fn($c) => $c->isActive())),
			'suspended' => count(array_filter($courses, fn($c) => !$c->isActive())),
		];

		return $this->render('admin/courses/index.html.twig', [
			'courses'          => $courses,
			'stats'            => $stats,
			'current_search'   => $search,
			'current_category' => $category,
			'current_status'   => $status,
		]);
	}

	#[Route('/create', name: 'admin_course_create')]
	public function create(Request $request) : Response{
		if($request->isMethod('POST')){
			$gym = $this->gymUserService->getPrimaryGym($this->getUser());

			if(!$gym){
				$this->addFlash('error', 'Nessuna palestra associata al tuo account.');

				return $this->redirectToRoute('admin_courses');
			}

			try{
				$this->courseService->createCourse($request->request->all(), $gym);
				$this->addFlash('success', 'Corso creato con successo.');

				return $this->redirectToRoute('admin_courses');
			}catch(\Exception $e){
				$this->addFlash('error', 'Errore nella creazione del corso: ' . $e->getMessage());
			}
		}

		return $this->render('admin/courses/create.html.twig', [
			'trainers'   => $this->courseService->getAvailableTrainers(),
			'categories' => $this->courseService->getCategories(),
		]);
	}

	#[Route('/{id}', name: 'admin_course_show', requirements: ['id' => '\d+'])]
	public function show(int $id) : Response{
		$course = $this->courseRepository->find($id);

		if (!$course) {
			$this->addFlash('error', 'Corso non trovato.');
			return $this->redirectToRoute('admin_courses');
		}

		$users = $this->gymUserService->getActiveMembers($course->getGym());

		return $this->render('admin/courses/show.html.twig', [
			'course' => $course,
			'users'  => $users,
		]);
	}

	#[Route('/session/{sessionId}', name: 'admin_course_session_show', requirements: ['sessionId' => '\d+'])]
	public function showSession(int $sessionId) : Response{
		$session = $this->sessionRepository->find($sessionId);

		if (!$session) {
			$this->addFlash('error', 'Sessione non trovata.');
			return $this->redirectToRoute('admin_courses_calendar');
		}

		$course = $session->getCourse();
		$users = $this->gymUserService->getActiveMembers($course->getGym());

		return $this->render('admin/courses/session_show.html.twig', [
			'session' => $session,
			'course'  => $course,
			'users'   => $users,
		]);
	}

	#[Route('/{id}/edit', name: 'admin_course_edit')]
	public function edit(int $id, Request $request) : Response{
		$course = $this->courseRepository->find($id);

		if (!$course) {
			$this->addFlash('error', 'Corso non trovato.');
			return $this->redirectToRoute('admin_courses');
		}

		try{
			if($request->isMethod('POST')){
				$this->courseService->updateCourse($course, $request->request->all());
				$this->addFlash('success', 'Corso aggiornato con successo.');

				return $this->redirectToRoute('admin_course_show', ['id' => $id]);
			}

			return $this->render('admin/courses/edit.html.twig', [
				'course'     => $course,
				'trainers'   => $this->courseService->getAvailableTrainers(),
				'categories' => $this->courseService->getCategories(),
			]);
		}catch(\Exception $e){
			$this->addFlash('error', $e->getMessage());
			return $this->redirectToRoute('admin_courses');
		}
	}

	#[Route('/{id}/schedule/add', name: 'admin_course_schedule_add', methods: ['POST'])]
	public function addSchedule(int $id, Request $request) : Response{
		$course = $this->courseRepository->find($id);

		if (!$course) {
			$this->addFlash('error', 'Corso non trovato.');
			return $this->redirectToRoute('admin_courses');
		}

		try{
			$this->courseService->addSchedule($course, $request->request->all());
			$this->addFlash('success', 'Orario aggiunto al corso.');
		}catch(\Exception $e){
			$this->addFlash('error', $e->getMessage());
		}

		return $this->redirectToRoute('admin_course_show', ['id' => $id]);
	}

	#[Route('/schedule/{scheduleId}/delete', name: 'admin_course_schedule_delete', methods: ['POST'])]
	public function deleteSchedule(int $scheduleId) : Response{
		try{
			// Use Case
			$schedule = $this->scheduleRepository->find($scheduleId);
			if(!$schedule){
				$this->addFlash('error', 'Orario non trovato.');
			}
			$courseId = $schedule->getCourse()->getId();

			$this->courseService->deleteSchedule($schedule);
			$this->addFlash('success', 'Orario rimosso.');

			return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
		}catch(\RuntimeException|\Exception $e){
			$this->addFlash('error', $e->getMessage());

			return $this->redirectToRoute('admin_courses');
		}
	}

	#[Route('/{id}/enroll', name: 'admin_course_enroll', methods: ['POST'])]
	public function enroll(int $id, Request $request) : Response{
		$course = $this->courseRepository->find($id);

		if (!$course) {
			$this->addFlash('error', 'Corso non trovato.');
			return $this->redirectToRoute('admin_courses');
		}

		try{
			$scheduleId = $request->request->getInt('schedule_id');
			$userId = $request->request->getInt('user_id');

			if(!$scheduleId || !$userId){
				throw new \RuntimeException('Seleziona utente e orario.');
			}

			$schedule = $this->scheduleRepository->find($scheduleId);
			if (!$schedule) {
				throw new \RuntimeException('Orario non trovato.');
			}

			$users = $this->gymUserService->getActiveMembers($course->getGym());
			$user = array_filter($users, fn($u) => $u->getId() == $userId)[0] ?? null;

			if(!$user){
				throw new \RuntimeException('Utente non valido.');
			}

			$this->enrollmentService->enrollUser($course, $schedule, $user);
			$this->addFlash('success', 'Utente iscritto all\'orario selezionato.');
		}catch(\RuntimeException $e){
			$this->addFlash('error', $e->getMessage());
		}

		return $this->redirectToRoute('admin_course_show', ['id' => $id]);
	}

	#[Route('/session/{sessionId}/enroll', name: 'admin_course_session_enroll', methods: ['POST'])]
	public function enrollToSession(int $sessionId, Request $request) : Response{
		$session = $this->sessionRepository->find($sessionId);

		if (!$session) {
			$this->addFlash('error', 'Sessione non trovata.');
			return $this->redirectToRoute('admin_courses_calendar');
		}

		try{
			$userId = $request->request->getInt('user_id');

			if(!$userId){
				throw new \RuntimeException('Seleziona un utente.');
			}

			$course = $session->getCourse();
			$users = $this->gymUserService->getActiveMembers($course->getGym());
			$user = array_filter($users, fn($u) => $u->getId() == $userId)[0] ?? null;

			if(!$user){
				throw new \RuntimeException('Utente non valido.');
			}

			// Usa il servizio esistente ma con la sessione
			$this->enrollmentService->enrollUserToSession($session, $user);
			$this->addFlash('success', 'Utente iscritto alla sessione.');
		}catch(\RuntimeException $e){
			$this->addFlash('error', $e->getMessage());
		}

		return $this->redirectToRoute('admin_course_session_show', ['sessionId' => $sessionId]);
	}

	#[Route('/enrollment/{enrollmentId}/cancel', name: 'admin_course_enrollment_cancel', methods: ['POST'])]
	public function cancelEnrollment(int $enrollmentId) : Response{
		$enrollment = $this->enrollmentRepository->find($enrollmentId);

		if (!$enrollment) {
			$this->addFlash('error', 'Iscrizione non trovata.');
			return $this->redirectToRoute('admin_courses');
		}

		try{
			$courseId = $enrollment->getCourse()->getId();
			$sessionId = $enrollment->getSession()?->getId();

			$this->enrollmentService->cancelEnrollment($enrollment);
			$this->addFlash('success', 'Iscrizione cancellata.');

			// Redirect alla sessione se esiste, altrimenti al corso
			if($sessionId){
				return $this->redirectToRoute('admin_course_session_show', ['sessionId' => $sessionId]);
			}

			return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
		}catch(\Exception $e){
			$this->addFlash('error', $e->getMessage());
			return $this->redirectToRoute('admin_courses');
		}
	}
}
