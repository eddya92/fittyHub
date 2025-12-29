<?php

namespace App\Controller\Admin;

use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Repository\GymCourseRepository;
use App\Domain\Course\Repository\CourseScheduleRepository;
use App\Domain\Course\Repository\CourseEnrollmentRepository;
use App\Domain\Course\Repository\CourseCategoryRepository;
use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use App\Domain\User\Repository\UserRepository;
use App\Domain\Gym\Repository\GymRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/courses')]
class CourseController extends AbstractController
{
    #[Route('/calendar', name: 'admin_courses_calendar')]
    public function calendar(
        GymCourseRepository $courseRepo,
        CourseCategoryRepository $categoryRepo,
        \App\Domain\Gym\Repository\GymRepository $gymRepo,
        \App\Domain\Gym\Repository\GymSettingsRepository $settingsRepo
    ): Response {
        // Prende tutti i corsi attivi con i loro orari
        $courses = $courseRepo->createQueryBuilder('c')
            ->leftJoin('c.schedules', 's')
            ->leftJoin('c.instructor', 'i')
            ->leftJoin('i.user', 'u')
            ->leftJoin('c.category', 'cat')
            ->where('c.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        // Organizza per giorno della settimana
        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $calendar = [];

        foreach ($weekDays as $day) {
            $calendar[$day] = [];
        }

        foreach ($courses as $course) {
            foreach ($course->getSchedules() as $schedule) {
                $day = $schedule->getDayOfWeek();
                if (!isset($calendar[$day])) {
                    $calendar[$day] = [];
                }
                $calendar[$day][] = [
                    'course' => $course,
                    'schedule' => $schedule
                ];
            }
        }

        // Ordina per ora
        foreach ($calendar as $day => $sessions) {
            usort($calendar[$day], function($a, $b) {
                return $a['schedule']->getStartTime() <=> $b['schedule']->getStartTime();
            });
        }

        // Prende tutte le categorie per la legenda
        $categories = $categoryRepo->findBy([], ['name' => 'ASC']);

        // Prende le impostazioni orari
        $user = $this->getUser();
        $gyms = $gymRepo->createQueryBuilder('g')
            ->join('g.admins', 'a')
            ->where('a = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $settings = null;
        $timeSlots = [];
        if (!empty($gyms)) {
            $settings = $settingsRepo->findOneBy(['gym' => $gyms[0]]);
            if (!$settings) {
                $settings = new \App\Domain\Gym\Entity\GymSettings();
                $settings->setGym($gyms[0]);
                $settingsRepo->save($settings, true);
            }
            $timeSlots = $settings->getTimeSlots();
        }

        return $this->render('admin/courses/calendar.html.twig', [
            'calendar' => $calendar,
            'categories' => $categories,
            'timeSlots' => $timeSlots,
            'settings' => $settings,
        ]);
    }

    #[Route('/', name: 'admin_courses')]
    public function index(
        Request $request,
        GymCourseRepository $courseRepo
    ): Response {
        $search = $request->query->get('search');
        $category = $request->query->get('category');
        $status = $request->query->get('status');

        $qb = $courseRepo->createQueryBuilder('c')
            ->leftJoin('c.gym', 'g')
            ->leftJoin('c.instructor', 'i')
            ->orderBy('c.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $qb->andWhere('c.category = :category')
               ->setParameter('category', $category);
        }

        if ($status) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $status);
        }

        $courses = $qb->getQuery()->getResult();

        $stats = [
            'total' => $courseRepo->count([]),
            'active' => $courseRepo->count(['status' => 'active']),
            'suspended' => $courseRepo->count(['status' => 'suspended']),
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
    public function create(
        Request $request,
        GymCourseRepository $courseRepo,
        PersonalTrainerRepository $trainerRepo,
        GymRepository $gymRepo,
        CourseCategoryRepository $categoryRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $course = new GymCourse();

            // Dati base corso
            $course->setName($request->request->get('name'));
            $course->setDescription($request->request->get('description'));

            // Categoria
            if ($categoryId = $request->request->get('category_id')) {
                $category = $categoryRepo->find($categoryId);
                if ($category) {
                    $course->setCategory($category);
                }
            }

            $course->setMaxParticipants((int)$request->request->get('max_participants'));
            $course->setStatus($request->request->get('status', 'active'));

            // Associa alla palestra dell'admin loggato
            $user = $this->getUser();
            $gyms = $gymRepo->createQueryBuilder('g')
                ->join('g.admins', 'a')
                ->where('a = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            if (empty($gyms)) {
                $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
                return $this->redirectToRoute('admin_courses');
            }

            $course->setGym($gyms[0]);

            // Istruttore (opzionale)
            if ($instructorId = $request->request->get('instructor_id')) {
                $instructor = $trainerRepo->find($instructorId);
                if ($instructor) {
                    $course->setInstructor($instructor);
                }
            }

            $courseRepo->save($course, true);

            $this->addFlash('success', 'Corso creato con successo.');
            return $this->redirectToRoute('admin_courses');
        }

        $trainers = $trainerRepo->findAll();
        $categories = $categoryRepo->findBy([], ['name' => 'ASC']);

        return $this->render('admin/courses/create.html.twig', [
            'trainers' => $trainers,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}', name: 'admin_course_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        GymCourseRepository $courseRepo,
        \App\Domain\Membership\Repository\MembershipRepository $membershipRepo
    ): Response {
        $course = $courseRepo->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        // Prende tutti gli utenti con membership attiva della stessa palestra del corso
        $users = $membershipRepo->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->where('m.gym = :gym')
            ->andWhere('m.status = :status')
            ->setParameter('gym', $course->getGym())
            ->setParameter('status', 'active')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/courses/show.html.twig', [
            'course' => $course,
            'users' => array_map(fn($m) => $m->getUser(), $users),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_course_edit')]
    public function edit(
        int $id,
        Request $request,
        GymCourseRepository $courseRepo,
        PersonalTrainerRepository $trainerRepo,
        CourseCategoryRepository $categoryRepo
    ): Response {
        $course = $courseRepo->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        if ($request->isMethod('POST')) {
            $course->setName($request->request->get('name'));
            $course->setDescription($request->request->get('description'));

            // Categoria
            if ($categoryId = $request->request->get('category_id')) {
                $category = $categoryRepo->find($categoryId);
                if ($category) {
                    $course->setCategory($category);
                }
            }

            $course->setMaxParticipants((int)$request->request->get('max_participants'));
            $course->setStatus($request->request->get('status'));

            if ($instructorId = $request->request->get('instructor_id')) {
                $instructor = $trainerRepo->find($instructorId);
                $course->setInstructor($instructor);
            } else {
                $course->setInstructor(null);
            }

            $courseRepo->save($course, true);

            $this->addFlash('success', 'Corso aggiornato con successo.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        $trainers = $trainerRepo->findAll();
        $categories = $categoryRepo->findBy([], ['name' => 'ASC']);

        return $this->render('admin/courses/edit.html.twig', [
            'course' => $course,
            'trainers' => $trainers,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/schedule/add', name: 'admin_course_schedule_add', methods: ['POST'])]
    public function addSchedule(
        int $id,
        Request $request,
        GymCourseRepository $courseRepo,
        CourseScheduleRepository $scheduleRepo
    ): Response {
        $course = $courseRepo->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        $schedule = new CourseSchedule();
        $schedule->setCourse($course);
        $schedule->setDayOfWeek($request->request->get('day_of_week'));
        $schedule->setStartTime(new \DateTime($request->request->get('start_time')));
        $schedule->setEndTime(new \DateTime($request->request->get('end_time')));

        $scheduleRepo->save($schedule, true);

        $this->addFlash('success', 'Orario aggiunto al corso.');
        return $this->redirectToRoute('admin_course_show', ['id' => $id]);
    }

    #[Route('/schedule/{scheduleId}/delete', name: 'admin_course_schedule_delete', methods: ['POST'])]
    public function deleteSchedule(
        int $scheduleId,
        CourseScheduleRepository $scheduleRepo
    ): Response {
        $schedule = $scheduleRepo->find($scheduleId);

        if (!$schedule) {
            $this->addFlash('error', 'Orario non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        $courseId = $schedule->getCourse()->getId();
        $scheduleRepo->remove($schedule, true);

        $this->addFlash('success', 'Orario rimosso.');
        return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
    }

    #[Route('/{id}/enroll', name: 'admin_course_enroll', methods: ['POST'])]
    public function enroll(
        int $id,
        Request $request,
        GymCourseRepository $courseRepo,
        CourseScheduleRepository $scheduleRepo,
        CourseEnrollmentRepository $enrollmentRepo,
        UserRepository $userRepo
    ): Response {
        $course = $courseRepo->find($id);

        if (!$course) {
            $this->addFlash('error', 'Corso non trovato.');
            return $this->redirectToRoute('admin_courses');
        }

        $scheduleId = $request->request->get('schedule_id');
        if (!$scheduleId) {
            $this->addFlash('error', 'Seleziona un orario.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        $schedule = $scheduleRepo->find($scheduleId);
        if (!$schedule || $schedule->getCourse()->getId() !== $course->getId()) {
            $this->addFlash('error', 'Orario non valido.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        if (!$schedule->hasAvailableSpots()) {
            $this->addFlash('error', 'Orario al completo.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        $userId = $request->request->get('user_id');
        $user = $userRepo->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utente non trovato.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        // Verifica se già iscritto a QUESTO orario specifico
        $existing = $enrollmentRepo->findOneBy([
            'schedule' => $schedule,
            'user' => $user,
            'status' => 'active'
        ]);

        if ($existing) {
            $this->addFlash('error', 'Utente già iscritto a questo orario.');
            return $this->redirectToRoute('admin_course_show', ['id' => $id]);
        }

        $enrollment = new CourseEnrollment();
        $enrollment->setCourse($course);
        $enrollment->setSchedule($schedule);
        $enrollment->setUser($user);
        $enrollment->setStatus('active');

        $enrollmentRepo->save($enrollment, true);

        $this->addFlash('success', 'Utente iscritto all\'orario selezionato.');
        return $this->redirectToRoute('admin_course_show', ['id' => $id]);
    }

    #[Route('/enrollment/{enrollmentId}/cancel', name: 'admin_course_enrollment_cancel', methods: ['POST'])]
    public function cancelEnrollment(
        int $enrollmentId,
        CourseEnrollmentRepository $enrollmentRepo
    ): Response {
        $enrollment = $enrollmentRepo->find($enrollmentId);

        if (!$enrollment) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_courses');
        }

        $courseId = $enrollment->getCourse()->getId();
        $enrollment->setStatus('cancelled');
        $enrollment->setCancelledAt(new \DateTimeImmutable());
        $enrollmentRepo->save($enrollment, true);

        $this->addFlash('success', 'Iscrizione cancellata.');
        return $this->redirectToRoute('admin_course_show', ['id' => $courseId]);
    }
}
