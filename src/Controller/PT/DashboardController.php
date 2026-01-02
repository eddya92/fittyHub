<?php

namespace App\Controller\PT;

use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use App\Domain\Workout\Repository\WorkoutPlanRepositoryInterface;
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
        TrainerRepositoryInterface $trainerRepo,
        PTClientRelationRepositoryInterface $relationRepo,
        WorkoutPlanRepositoryInterface $workoutRepo
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

        // Clienti attivi senza schede di allenamento
        $clientsWithoutPlans = $relationRepo->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->where('r.personalTrainer = :trainer')
            ->andWhere('r.status = :status')
            ->andWhere('NOT EXISTS (
                SELECT 1 FROM App\Domain\Workout\Entity\WorkoutPlan wp
                WHERE wp.client = c
                AND wp.personalTrainer = :trainer
                AND wp.status IN (:planStatuses)
            )')
            ->setParameter('trainer', $trainer)
            ->setParameter('status', 'active')
            ->setParameter('planStatuses', ['draft', 'active'])
            ->orderBy('r.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('pt/dashboard/index.html.twig', [
            'trainer' => $trainer,
            'stats' => $stats,
            'active_clients' => $activeClients,
            'recent_workout_plans' => $recentWorkoutPlans,
            'clients_without_plans' => $clientsWithoutPlans,
        ]);
    }
}
