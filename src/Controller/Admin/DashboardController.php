<?php

namespace App\Controller\Admin;

use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        GymRepositoryInterface $gymRepo,
        MembershipRepositoryInterface $membershipRepo,
        TrainerRepositoryInterface $trainerRepo
    ): Response {
        // Stats generali
        $stats = [
            'total_memberships' => $membershipRepo->count([]),
            'active_memberships' => $membershipRepo->count(['status' => 'active']),
            'total_trainers' => $trainerRepo->count([]),
        ];

        // Iscrizioni recenti
        $recentMemberships = $membershipRepo->findBy(
            [],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recent_memberships' => $recentMemberships,
        ]);
    }
}
