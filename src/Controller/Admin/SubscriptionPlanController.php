<?php

namespace App\Controller\Admin;

use App\Domain\Membership\UseCase\GetAllSubscriptionPlans;
use App\Domain\Membership\UseCase\GetSubscriptionPlanById;
use App\Domain\Membership\UseCase\CreateSubscriptionPlan;
use App\Domain\Membership\UseCase\UpdateSubscriptionPlan;
use App\Domain\Membership\UseCase\ToggleSubscriptionPlan;
use App\Domain\Membership\UseCase\DeleteSubscriptionPlan;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/subscription-plans')]
class SubscriptionPlanController extends AbstractController
{
    public function __construct(
        private GetAllSubscriptionPlans $getAllSubscriptionPlans,
        private GetSubscriptionPlanById $getSubscriptionPlanById,
        private CreateSubscriptionPlan $createSubscriptionPlan,
        private UpdateSubscriptionPlan $updateSubscriptionPlan,
        private ToggleSubscriptionPlan $toggleSubscriptionPlan,
        private DeleteSubscriptionPlan $deleteSubscriptionPlan,
        private GymRepositoryInterface $gymRepository
    ) {}

    #[Route('/', name: 'admin_subscription_plans')]
    public function index(): Response
    {
        return $this->render('admin/subscription_plans/index.html.twig', [
            'plans' => $this->getAllSubscriptionPlans->execute(),
        ]);
    }

    #[Route('/create', name: 'admin_subscription_plan_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        try {
            $gyms = $this->gymRepository->findAll();

            if ($request->isMethod('POST')) {
                $gymId = $request->request->getInt('gym_id');
                $gym = $this->gymRepository->find($gymId);

                if (!$gym) {
                    throw new \RuntimeException('Palestra non trovata.');
                }

                $this->createSubscriptionPlan->execute($gym, [
                    'name' => $request->request->get('name'),
                    'description' => $request->request->get('description'),
                    'duration' => $request->request->get('duration'),
                    'price' => $request->request->get('price'),
                    'include_pt' => $request->request->get('include_pt'),
                    'pt_sessions_included' => $request->request->get('pt_sessions_included'),
                    'max_access_per_week' => $request->request->get('max_access_per_week'),
                    'features' => $request->request->all('features'),
                ]);

                $this->addFlash('success', 'Piano abbonamento creato con successo!');
                return $this->redirectToRoute('admin_subscription_plans');
            }

            return $this->render('admin/subscription_plans/create.html.twig', [
                'gyms' => $gyms,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_subscription_plan_create');
        }
    }

    #[Route('/{id}/edit', name: 'admin_subscription_plan_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        try {
            $plan = $this->getSubscriptionPlanById->execute($id);
            $gyms = $this->gymRepository->findAll();

            if ($request->isMethod('POST')) {
                $gymId = $request->request->getInt('gym_id');
                $gym = $this->gymRepository->find($gymId);

                if (!$gym) {
                    throw new \RuntimeException('Palestra non trovata.');
                }

                $this->updateSubscriptionPlan->execute($plan, $gym, [
                    'name' => $request->request->get('name'),
                    'description' => $request->request->get('description'),
                    'duration' => $request->request->get('duration'),
                    'price' => $request->request->get('price'),
                    'include_pt' => $request->request->get('include_pt'),
                    'pt_sessions_included' => $request->request->get('pt_sessions_included'),
                    'max_access_per_week' => $request->request->get('max_access_per_week'),
                    'features' => $request->request->all('features'),
                ]);

                $this->addFlash('success', 'Piano abbonamento aggiornato con successo!');
                return $this->redirectToRoute('admin_subscription_plans');
            }

            return $this->render('admin/subscription_plans/edit.html.twig', [
                'plan' => $plan,
                'gyms' => $gyms,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_subscription_plans');
        }
    }

    #[Route('/{id}/toggle', name: 'admin_subscription_plan_toggle', methods: ['POST'])]
    public function toggle(int $id): Response
    {
        try {
            $plan = $this->getSubscriptionPlanById->execute($id);
            $this->toggleSubscriptionPlan->execute($plan);

            $status = $plan->isActive() ? 'attivato' : 'disattivato';
            $this->addFlash('success', "Piano abbonamento {$status} con successo!");
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_subscription_plans');
    }

    #[Route('/{id}/delete', name: 'admin_subscription_plan_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        try {
            $plan = $this->getSubscriptionPlanById->execute($id);
            $this->deleteSubscriptionPlan->execute($plan);

            $this->addFlash('success', 'Piano abbonamento eliminato con successo!');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_subscription_plans');
    }
}