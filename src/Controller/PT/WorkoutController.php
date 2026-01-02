<?php

namespace App\Controller\PT;

use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use App\Domain\Workout\Repository\WorkoutPlanRepository;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pt/workouts')]
#[IsGranted('ROLE_PT')]
class WorkoutController extends AbstractController
{
    #[Route('/', name: 'pt_workouts')]
    public function index(
        Request $request,
        TrainerRepositoryInterface $trainerRepo,
        WorkoutPlanRepository $workoutRepo
    ): Response {
        // Recupera il PT loggato
        $user = $this->getUser();
        $trainer = $trainerRepo->findOneBy(['user' => $user]);

        if (!$trainer) {
            $this->addFlash('error', 'Profilo Personal Trainer non trovato.');
            return $this->redirectToRoute('app_home');
        }

        // Filtri
        $status = $request->query->get('status');
        $search = $request->query->get('search');

        $qb = $workoutRepo->createQueryBuilder('w')
            ->leftJoin('w.user', 'u')
            ->where('w.personalTrainer = :trainer')
            ->setParameter('trainer', $trainer)
            ->orderBy('w.createdAt', 'DESC');

        // Filtro per status
        if ($status) {
            $qb->andWhere('w.status = :status')
               ->setParameter('status', $status);
        }

        // Filtro ricerca (nome piano o cliente)
        if ($search) {
            $qb->andWhere('w.name LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $workoutPlans = $qb->getQuery()->getResult();

        // Stats
        $stats = [
            'total' => $workoutRepo->count(['personalTrainer' => $trainer]),
            'active' => $workoutRepo->count(['personalTrainer' => $trainer, 'status' => 'active']),
            'completed' => $workoutRepo->count(['personalTrainer' => $trainer, 'status' => 'completed']),
        ];

        return $this->render('pt/workouts/index.html.twig', [
            'trainer' => $trainer,
            'workout_plans' => $workoutPlans,
            'stats' => $stats,
            'current_status' => $status,
            'current_search' => $search,
        ]);
    }

    #[Route('/create', name: 'pt_workouts_create')]
    public function create(
        Request $request,
        TrainerRepositoryInterface $trainerRepo,
        UserRepositoryInterface $userRepo
    ): Response {
        // Recupera il PT loggato
        $user = $this->getUser();
        $trainer = $trainerRepo->findOneBy(['user' => $user]);

        if (!$trainer) {
            $this->addFlash('error', 'Profilo Personal Trainer non trovato.');
            return $this->redirectToRoute('app_home');
        }

        // Get client ID from query params (if coming from client detail page)
        $clientId = $request->query->get('client');
        $selectedClient = null;

        if ($clientId) {
            $selectedClient = $userRepo->find($clientId);
        }

        // Get list of PT's clients for the dropdown
        $clients = $trainer->getClientRelations()
            ->filter(fn($relation) => $relation->getStatus() === 'active')
            ->map(fn($relation) => $relation->getClient())
            ->toArray();

        return $this->render('pt/workouts/create.html.twig', [
            'trainer' => $trainer,
            'clients' => $clients,
            'selected_client' => $selectedClient,
        ]);
    }

    #[Route('/{id}', name: 'pt_workout_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        TrainerRepositoryInterface $trainerRepo,
        WorkoutPlanRepository $workoutRepo
    ): Response {
        // Recupera il PT loggato
        $user = $this->getUser();
        $trainer = $trainerRepo->findOneBy(['user' => $user]);

        if (!$trainer) {
            $this->addFlash('error', 'Profilo Personal Trainer non trovato.');
            return $this->redirectToRoute('app_home');
        }

        $workoutPlan = $workoutRepo->find($id);

        if (!$workoutPlan || $workoutPlan->getPersonalTrainer() !== $trainer) {
            $this->addFlash('error', 'Piano di allenamento non trovato o non hai i permessi per visualizzarlo.');
            return $this->redirectToRoute('pt_workouts');
        }

        return $this->render('pt/workouts/show.html.twig', [
            'trainer' => $trainer,
            'workout_plan' => $workoutPlan,
        ]);
    }
}
