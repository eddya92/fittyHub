<?php

namespace App\Controller\PT;

use App\Domain\Workout\Entity\ClientAssessment;
use App\Domain\Workout\Entity\WorkoutPlan;
use App\Domain\Workout\Entity\WorkoutExercise;
use App\Domain\Workout\Entity\Exercise;
use App\Domain\Workout\Repository\ClientAssessmentRepositoryInterface;
use App\Domain\Workout\Repository\WorkoutPlanRepositoryInterface;
use App\Domain\Workout\Repository\ExerciseRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pt/workout-plans')]
#[IsGranted('ROLE_PT')]
class WorkoutPlanController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TrainerRepositoryInterface $ptRepository,
        private PTClientRelationRepositoryInterface $relationRepository,
        private ClientAssessmentRepositoryInterface $assessmentRepository,
        private WorkoutPlanRepositoryInterface $workoutPlanRepository,
        private UserRepositoryInterface $userRepository,
        private ExerciseRepositoryInterface $exerciseRepository,
    ) {}

    #[Route('', name: 'pt_workout_plans', methods: ['GET'])]
    public function index(): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);

        if (!$pt) {
            throw $this->createAccessDeniedException('Personal Trainer profile not found');
        }

        $workoutPlans = $this->workoutPlanRepository->findBy(
            ['personalTrainer' => $pt],
            ['createdAt' => 'DESC']
        );

        return $this->render('pt/workout_plans/index.html.twig', [
            'workoutPlans' => $workoutPlans,
        ]);
    }

    #[Route('/assessments', name: 'pt_assessments', methods: ['GET'])]
    public function assessments(): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);

        if (!$pt) {
            throw $this->createAccessDeniedException('Personal Trainer profile not found');
        }

        $assessments = $this->assessmentRepository->findByPersonalTrainer($pt);

        return $this->render('pt/workout_plans/assessments.html.twig', [
            'assessments' => $assessments,
        ]);
    }

    #[Route('/assessments/create/{clientId}', name: 'pt_assessment_create', methods: ['GET', 'POST'])]
    public function createAssessment(int $clientId, Request $request): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);

        if (!$pt) {
            throw $this->createAccessDeniedException('Personal Trainer profile not found');
        }

        $client = $this->userRepository->find($clientId);

        if (!$client) {
            throw $this->createNotFoundException('Client not found');
        }

        // Verifica che il cliente sia assegnato al PT
        $relation = $this->relationRepository->findOneBy([
            'personalTrainer' => $pt,
            'client' => $client,
        ]);

        if (!$relation) {
            throw $this->createAccessDeniedException('This client is not assigned to you');
        }

        if ($request->isMethod('POST')) {
            $assessment = new ClientAssessment();
            $assessment->setClient($client);
            $assessment->setPersonalTrainer($pt);

            // Helper per convertire valori in int o null
            $getIntOrNull = function(string $key) use ($request): ?int {
                $value = $request->request->get($key);
                if ($value === null || $value === '') {
                    return null;
                }
                return is_numeric($value) ? (int)$value : null;
            };

            // Dati anagrafici
            $assessment->setAge($getIntOrNull('age'));
            $assessment->setHeight($request->request->get('height') ?: null);
            $assessment->setWeight($request->request->get('weight') ?: null);
            $assessment->setGender($request->request->get('gender') ?: null);

            // Esperienza e obiettivi
            $assessment->setFitnessLevel($request->request->get('fitnessLevel') ?: null);
            $assessment->setPrimaryGoal($request->request->get('primaryGoal') ?: null);
            $assessment->setSecondaryGoals($request->request->get('secondaryGoals') ?: null);
            $assessment->setTrainingExperience($getIntOrNull('trainingExperience'));
            $assessment->setWeeklyAvailability($getIntOrNull('weeklyAvailability'));
            $assessment->setSessionDuration($getIntOrNull('sessionDuration'));

            // Infortuni e salute
            $currentInjuries = array_filter($request->request->all('currentInjuries') ?? []);
            $assessment->setCurrentInjuries($currentInjuries);

            $pastInjuries = array_filter($request->request->all('pastInjuries') ?? []);
            $assessment->setPastInjuries($pastInjuries);

            $medicalConditions = array_filter($request->request->all('medicalConditions') ?? []);
            $assessment->setMedicalConditions($medicalConditions);

            $assessment->setMedications($request->request->get('medications') ?: null);
            $assessment->setAllergies($request->request->get('allergies') ?: null);

            // Stile di vita
            $assessment->setActivityLevel($request->request->get('activityLevel') ?: null);
            $assessment->setOccupation($request->request->get('occupation') ?: null);
            $assessment->setSleepHours($getIntOrNull('sleepHours'));
            $assessment->setStressLevel($getIntOrNull('stressLevel'));
            $assessment->setNutritionHabits($request->request->get('nutritionHabits') ?: null);

            // Preferenze
            $preferredExercises = array_filter($request->request->all('preferredExercises') ?? []);
            $assessment->setPreferredExercises($preferredExercises);

            $dislikedExercises = array_filter($request->request->all('dislikedExercises') ?? []);
            $assessment->setDislikedExercises($dislikedExercises);

            $assessment->setTrainingPreferences($request->request->get('trainingPreferences') ?: null);

            // Valutazioni fisiche
            $assessment->setBodyFatPercentage($request->request->get('bodyFatPercentage') ?: null);
            $assessment->setMuscleMass($request->request->get('muscleMass') ?: null);

            // Circonferenze
            $circumferences = [];
            if ($chest = $request->request->get('chest')) $circumferences['chest'] = $chest;
            if ($waist = $request->request->get('waist')) $circumferences['waist'] = $waist;
            if ($hips = $request->request->get('hips')) $circumferences['hips'] = $hips;
            if ($arms = $request->request->get('arms')) $circumferences['arms'] = $arms;
            if ($thighs = $request->request->get('thighs')) $circumferences['thighs'] = $thighs;
            $assessment->setCircumferences($circumferences);

            // Note PT
            $assessment->setPtNotes($request->request->get('ptNotes') ?: null);

            // Completa se richiesto
            if ($request->request->get('complete') === '1') {
                $assessment->markAsCompleted();
            }

            $this->entityManager->persist($assessment);
            $this->entityManager->flush();

            $this->addFlash('success', 'Assessment salvato con successo');

            if ($assessment->getStatus() === 'completed') {
                return $this->redirectToRoute('pt_workout_plan_create_from_assessment', [
                    'assessmentId' => $assessment->getId()
                ]);
            }

            return $this->redirectToRoute('pt_assessments');
        }

        return $this->render('pt/workout_plans/assessment_create.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/assessments/{id}', name: 'pt_assessment_view', methods: ['GET'])]
    public function viewAssessment(ClientAssessment $assessment): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);

        if (!$pt || $assessment->getPersonalTrainer() !== $pt) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('pt/workout_plans/assessment_view.html.twig', [
            'assessment' => $assessment,
        ]);
    }

    #[Route('/create-from-assessment/{assessmentId}', name: 'pt_workout_plan_create_from_assessment', methods: ['GET', 'POST'])]
    public function createFromAssessment(int $assessmentId, Request $request): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);

        if (!$pt) {
            throw $this->createAccessDeniedException('Personal Trainer profile not found');
        }

        $assessment = $this->assessmentRepository->find($assessmentId);

        if (!$assessment || $assessment->getPersonalTrainer() !== $pt) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            // Helper per convertire valori in int o null
            $getIntOrNull = function(string $key) use ($request): ?int {
                $value = $request->request->get($key);
                if ($value === null || $value === '') {
                    return null;
                }
                return is_numeric($value) ? (int)$value : null;
            };

            $plan = new WorkoutPlan();
            $plan->setClient($assessment->getClient());
            $plan->setPersonalTrainer($pt);
            $plan->setName($request->request->get('name'));
            $plan->setDescription($request->request->get('description'));
            $plan->setPlanType($request->request->get('planType'));
            $plan->setGoal($assessment->getPrimaryGoal());
            $plan->setStartDate(new \DateTime($request->request->get('startDate')));

            if ($endDate = $request->request->get('endDate')) {
                $plan->setEndDate(new \DateTime($endDate));
            }

            $plan->setWeeksCount($getIntOrNull('weeksCount') ?? 4);
            $plan->setNotes($request->request->get('notes'));

            $this->entityManager->persist($plan);
            $this->entityManager->flush();

            $this->addFlash('success', 'Piano di allenamento creato! Ora aggiungi gli esercizi.');

            return $this->redirectToRoute('pt_workout_plan_edit', ['id' => $plan->getId()]);
        }

        return $this->render('pt/workout_plans/create_from_assessment.html.twig', [
            'assessment' => $assessment,
        ]);
    }

    #[Route('/{id}/edit', name: 'pt_workout_plan_edit', methods: ['GET'])]
    public function edit(WorkoutPlan $plan): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);

        if (!$pt || $plan->getPersonalTrainer() !== $pt) {
            throw $this->createAccessDeniedException();
        }

        $exercises = $this->exerciseRepository->findAllActive();
        $categories = $this->exerciseRepository->getAllCategories();

        return $this->render('pt/workout_plans/edit.html.twig', [
            'plan' => $plan,
            'exercises' => $exercises,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/add-exercise', name: 'pt_workout_plan_add_exercise', methods: ['POST'])]
    public function addExercise(WorkoutPlan $plan, Request $request): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);

        if (!$pt || $plan->getPersonalTrainer() !== $pt) {
            throw $this->createAccessDeniedException();
        }

        // Helper per convertire valori in int con default
        $getIntOrDefault = function(string $key, int $default) use ($request): int {
            $value = $request->request->get($key);
            if ($value === null || $value === '') {
                return $default;
            }
            return is_numeric($value) ? (int)$value : $default;
        };

        $exerciseId = $getIntOrDefault('exerciseId', 0);
        $exercise = $this->exerciseRepository->find($exerciseId);

        if (!$exercise) {
            $this->addFlash('error', 'Esercizio non trovato');
            return $this->redirectToRoute('pt_workout_plan_edit', ['id' => $plan->getId()]);
        }

        $workoutExercise = new WorkoutExercise();
        $workoutExercise->setWorkoutPlan($plan);
        $workoutExercise->setExercise($exercise);
        $workoutExercise->setName($exercise->getName());
        $workoutExercise->setDayOfWeek($getIntOrDefault('dayOfWeek', 1));
        $workoutExercise->setWeek($getIntOrDefault('week', 1));
        $workoutExercise->setSets($getIntOrDefault('sets', 3));
        $workoutExercise->setReps($request->request->get('reps') ?: '10');
        $workoutExercise->setRestTime($getIntOrDefault('restTime', 60));

        if ($weight = $request->request->get('weight')) {
            $workoutExercise->setWeight($weight);
        }
        if ($tempo = $request->request->get('tempo')) {
            $workoutExercise->setTempo($tempo);
        }
        if ($notes = $request->request->get('notes')) {
            $workoutExercise->setNotes($notes);
        }

        // Get max order index for this day/week and increment
        $maxOrder = $this->entityManager->createQueryBuilder()
            ->select('MAX(we.orderIndex)')
            ->from(WorkoutExercise::class, 'we')
            ->where('we.workoutPlan = :plan')
            ->andWhere('we.dayOfWeek = :day')
            ->andWhere('we.week = :week')
            ->setParameter('plan', $plan)
            ->setParameter('day', $workoutExercise->getDayOfWeek())
            ->setParameter('week', $workoutExercise->getWeek())
            ->getQuery()
            ->getSingleScalarResult();

        $workoutExercise->setOrderIndex(($maxOrder ?? -1) + 1);

        $this->entityManager->persist($workoutExercise);
        $this->entityManager->flush();

        $this->addFlash('success', 'Esercizio aggiunto al piano!');

        return $this->redirectToRoute('pt_workout_plan_edit', ['id' => $plan->getId()]);
    }

    #[Route('/{planId}/remove-exercise/{exerciseId}', name: 'pt_workout_plan_remove_exercise', methods: ['POST'])]
    public function removeExercise(int $planId, int $exerciseId): Response
    {
        $pt = $this->ptRepository->findOneBy(['user' => $this->getUser()]);
        $plan = $this->workoutPlanRepository->find($planId);

        if (!$pt || !$plan || $plan->getPersonalTrainer() !== $pt) {
            throw $this->createAccessDeniedException();
        }

        $workoutExercise = $this->entityManager->getRepository(WorkoutExercise::class)->find($exerciseId);

        if (!$workoutExercise || $workoutExercise->getWorkoutPlan() !== $plan) {
            throw $this->createNotFoundException();
        }

        $this->entityManager->remove($workoutExercise);
        $this->entityManager->flush();

        $this->addFlash('success', 'Esercizio rimosso dal piano');

        return $this->redirectToRoute('pt_workout_plan_edit', ['id' => $plan->getId()]);
    }
}