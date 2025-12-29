<?php

namespace App\Controller\PT;

use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepository;
use App\Domain\Workout\Repository\WorkoutPlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pt')]
#[IsGranted('ROLE_PT')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'pt_dashboard')]
    public function index(
        PersonalTrainerRepository $trainerRepo,
        PTClientRelationRepository $relationRepo,
        WorkoutPlanRepository $workoutRepo
    ): Response {
        // Recupera il PT loggato
        $user = $this->getUser();
        $trainer = $trainerRepo->findOneBy(['user' => $user]);

        if (!$trainer) {
            $this->addFlash('error', 'Profilo Personal Trainer non trovato.');
            return $this->redirectToRoute('app_home');
        }

        // Statistiche
        $stats = [
            'total_clients' => $relationRepo->count(['personalTrainer' => $trainer]),
            'active_clients' => $relationRepo->count(['personalTrainer' => $trainer, 'status' => 'active']),
            'total_workout_plans' => $workoutRepo->count(['personalTrainer' => $trainer]),
        ];

        // Clienti attivi (ultimi 5)
        $activeClients = $relationRepo->findBy(
            ['personalTrainer' => $trainer, 'status' => 'active'],
            ['startDate' => 'DESC'],
            5
        );

        // Piani allenamento recenti (ultimi 5)
        $recentWorkoutPlans = $workoutRepo->findBy(
            ['personalTrainer' => $trainer],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('pt/dashboard/index.html.twig', [
            'trainer' => $trainer,
            'stats' => $stats,
            'active_clients' => $activeClients,
            'recent_workout_plans' => $recentWorkoutPlans,
        ]);
    }
}
