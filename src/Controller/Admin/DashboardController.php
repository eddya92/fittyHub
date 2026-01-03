<?php

namespace App\Controller\Admin;

use App\Domain\Course\Repository\CourseRepositoryInterface;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;
use App\Domain\Course\Repository\CourseWaitingListRepositoryInterface;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\Invitation\Repository\InvitationRepositoryInterface;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\GymUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    public function __construct(
        private GymUserService $gymUserService
    ) {}

    #[Route('/', name: 'admin_dashboard')]
    public function index(
        GymRepositoryInterface $gymRepo,
        MembershipRepositoryInterface $membershipRepo,
        TrainerRepositoryInterface $trainerRepo,
        UserRepositoryInterface $userRepo,
        CourseRepositoryInterface $courseRepo,
        MedicalCertificateRepositoryInterface $certificateRepo,
        InvitationRepositoryInterface $invitationRepo,
        MembershipRequestRepositoryInterface $requestRepo,
        GymAttendanceRepositoryInterface $attendanceRepo,
        CourseScheduleRepositoryInterface $scheduleRepo,
        CourseWaitingListRepositoryInterface $waitingListRepo
    ): Response {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        $in30Days = new \DateTime('+30 days');

        // Stats generali
        $stats = [
            'total_memberships' => $membershipRepo->count([]),
            'active_memberships' => $membershipRepo->count(['status' => 'active']),
            'total_trainers' => $trainerRepo->count([]),
            'total_users' => $userRepo->count([]),
            'active_courses' => $courseRepo->count(['status' => 'active']),
        ];

        // ALERT: Certificati medici scaduti o in scadenza (30gg)
        $allCertificates = $certificateRepo->findAll();
        $expiredCertificates = array_filter($allCertificates, function($cert) use ($today) {
            return $cert->getExpiryDate() < $today;
        });
        $expiringCertificates = array_filter($allCertificates, function($cert) use ($today, $in30Days) {
            return $cert->getExpiryDate() >= $today && $cert->getExpiryDate() <= $in30Days;
        });

        // ALERT: Abbonamenti in scadenza (30gg)
        $activeMemberships = $membershipRepo->findBy(['status' => 'active']);
        $expiringMemberships = array_filter($activeMemberships, function($m) use ($in30Days) {
            return $m->getEndDate() <= $in30Days;
        });

        // ALERT: Inviti PT pendenti
        $pendingInvitations = $invitationRepo->findBy(['status' => 'pending']);

        // ALERT: Richieste iscrizione in attesa
        $pendingRequests = $requestRepo->findBy(['status' => 'pending']);

        // Check-in oggi
        $todayAttendances = [];
        if ($gym) {
            $allAttendances = $attendanceRepo->findBy(['gym' => $gym], ['checkInTime' => 'DESC']);
            $todayAttendances = array_filter($allAttendances, function($a) use ($today, $tomorrow) {
                return $a->getCheckInTime() >= $today && $a->getCheckInTime() < $tomorrow;
            });
        }

        // Corsi oggi/domani
        $todaySchedules = [];
        $currentDayOfWeek = strtolower($today->format('l'));
        $allSchedules = $scheduleRepo->findAll();
        foreach ($allSchedules as $schedule) {
            if ($schedule->getDayOfWeek() === $currentDayOfWeek && $schedule->getCourse()->getStatus() === 'active') {
                $todaySchedules[] = $schedule;
            }
        }

        // Ordina per orario
        usort($todaySchedules, function($a, $b) {
            return $a->getStartTime() <=> $b->getStartTime();
        });

        // Liste d'attesa attive (con almeno 1 persona)
        $activeWaitingLists = [];
        foreach ($allSchedules as $schedule) {
            $waitingCount = $waitingListRepo->countWaitingBySchedule($schedule);
            if ($waitingCount > 0) {
                $activeWaitingLists[] = [
                    'schedule' => $schedule,
                    'count' => $waitingCount,
                ];
            }
        }

        // Iscrizioni recenti
        $recentMemberships = $membershipRepo->findBy(
            [],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recent_memberships' => $recentMemberships,
            'alerts' => [
                'expired_certificates' => $expiredCertificates,
                'expiring_certificates' => $expiringCertificates,
                'expiring_memberships' => $expiringMemberships,
                'pending_invitations' => $pendingInvitations,
                'pending_requests' => $pendingRequests,
            ],
            'today_check_ins' => count($todayAttendances),
            'today_schedules' => $todaySchedules,
            'active_waiting_lists' => $activeWaitingLists,
        ]);
    }
}
