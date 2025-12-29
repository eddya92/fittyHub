<?php

namespace App\Controller\Admin;

use App\Application\Service\CheckInService;
use App\Application\Service\GymUserService;
use App\Domain\Gym\Repository\GymAttendanceRepository;
use App\Domain\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/check-in')]
class CheckInController extends AbstractController
{
    public function __construct(
        private CheckInService $checkInService,
        private GymUserService $gymUserService,
        private GymAttendanceRepository $attendanceRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/', name: 'admin_check_in')]
    public function index(): Response
    {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        if (!$gym) {
            $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
            return $this->redirectToRoute('admin_dashboard');
        }

        // Ottieni le ultime 20 presenze
        $recentAttendances = $this->attendanceRepository->createQueryBuilder('a')
            ->where('a.gym = :gym')
            ->andWhere('a.type = :type')
            ->setParameter('gym', $gym)
            ->setParameter('type', 'gym_entrance')
            ->orderBy('a.checkInTime', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        // Statistiche oggi
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $todayStats = $this->checkInService->getAttendanceStats($gym, $today, $tomorrow);

        return $this->render('admin/check_in/index.html.twig', [
            'gym' => $gym,
            'recentAttendances' => $recentAttendances,
            'todayStats' => $todayStats,
        ]);
    }

    #[Route('/scan', name: 'admin_check_in_scan')]
    public function scan(): Response
    {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        if (!$gym) {
            $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/check_in/scan.html.twig', [
            'gym' => $gym,
        ]);
    }

    #[Route('/process', name: 'admin_check_in_process', methods: ['POST'])]
    public function process(Request $request): Response
    {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        if (!$gym) {
            return $this->json([
                'success' => false,
                'message' => 'Nessuna palestra associata al tuo account.'
            ], 403);
        }

        $userId = $request->request->get('user_id');
        $email = $request->request->get('email');

        // Trova l'utente per ID o email
        $user = null;
        if ($userId) {
            $user = $this->userRepository->find($userId);
        } elseif ($email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
        }

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utente non trovato.'
            ], 404);
        }

        // Verifica se può fare check-in
        $validation = $this->checkInService->canCheckIn($user, $gym);

        if (!$validation['allowed']) {
            return $this->json([
                'success' => false,
                'message' => $validation['reason'],
                'user' => [
                    'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                    'email' => $user->getEmail()
                ]
            ], 403);
        }

        // Effettua check-in
        $attendance = $this->checkInService->checkIn($user, $gym);

        return $this->json([
            'success' => true,
            'message' => 'Ingresso consentito!',
            'user' => [
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'email' => $user->getEmail(),
                'checkInTime' => $attendance->getCheckInTime()->format('H:i')
            ]
        ]);
    }

    #[Route('/history/{userId}', name: 'admin_check_in_history')]
    public function history(int $userId): Response
    {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        if (!$gym) {
            $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utente non trovato.');
            return $this->redirectToRoute('admin_check_in');
        }

        $history = $this->checkInService->getUserAttendanceHistory($user, $gym, 50);

        return $this->render('admin/check_in/history.html.twig', [
            'gym' => $gym,
            'user' => $user,
            'history' => $history,
        ]);
    }
}