<?php

namespace App\Controller\Admin;

use App\Domain\Membership\UseCase\GetMembershipById;
use App\Domain\Membership\UseCase\SearchMemberships;
use App\Domain\Membership\UseCase\CancelMembership;
use App\Domain\Membership\UseCase\RenewMembership;
use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ESEMPIO Controller DDD
 * Controller pulito che usa solo Use Cases
 * Ogni metodo è semplicissimo: prende dati HTTP e chiama il Use Case
 */
#[Route('/admin/memberships-ddd')]
class MembershipControllerDDD extends AbstractController
{
    public function __construct(
        private SearchMemberships $searchMemberships,
        private GetMembershipById $getMembershipById,
        private CancelMembership $cancelMembership,
        private RenewMembership $renewMembership,
        private EnrollmentRepositoryInterface $enrollmentRepository,
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    /**
     * Lista abbonamenti con filtri e paginazione
     */
    #[Route('/', name: 'admin_memberships_ddd')]
    public function index(Request $request): Response
    {
        // Prende parametri HTTP
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $gymId = $request->query->getInt('gym');
        $page = max(1, $request->query->getInt('page', 1));

        // Esegue Use Case
        $result = $this->searchMemberships->execute($status, $search, $gymId, $page);

        // Aggiungi quote iscrizione
        $membershipsWithEnrollment = [];
        foreach ($result['memberships'] as $membership) {
            $enrollment = $this->enrollmentRepository->findActiveEnrollment(
                $membership->getUser(),
                $membership->getGym()
            );

            $membershipsWithEnrollment[] = [
                'membership' => $membership,
                'enrollment' => $enrollment,
            ];
        }

        // Renderizza vista
        return $this->render('admin/memberships/index.html.twig', [
            'memberships' => $membershipsWithEnrollment,
            'current_status' => $status,
            'current_search' => $search,
            'current_gym' => $gymId,
            'current_page' => $result['current_page'],
            'total_pages' => $result['total_pages'],
            'total_users' => $result['total_users'],
        ]);
    }

    /**
     * Cancella abbonamento
     */
    #[Route('/{id}/cancel', name: 'admin_membership_cancel_ddd', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        try {
            // Ottiene abbonamento (Use Case)
            $membership = $this->getMembershipById->execute($id);

            // Cancella (Use Case)
            $this->cancelMembership->execute($membership);

            $this->addFlash('success', 'Iscrizione cancellata con successo.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    /**
     * Rinnova abbonamento
     */
    #[Route('/{id}/renew', name: 'admin_membership_renew_ddd', methods: ['GET', 'POST'])]
    public function renew(int $id, Request $request): Response
    {
        try {
            // Ottiene abbonamento (Use Case)
            $currentMembership = $this->getMembershipById->execute($id);
            $subscriptionPlans = $this->planRepository->findBy([
                'gym' => $currentMembership->getGym(),
                'isActive' => true
            ]);

            if ($request->isMethod('POST')) {
                $planId = $request->request->getInt('subscription_plan_id');

                if (!$planId) {
                    throw new \RuntimeException('Seleziona un piano abbonamento.');
                }

                $plan = $this->planRepository->find($planId);
                if (!$plan) {
                    throw new \RuntimeException('Piano abbonamento non trovato.');
                }

                // Rinnova (Use Case) - CHIARISSIMO cosa viene passato
                $newMembership = $this->renewMembership->execute(
                    currentMembership: $currentMembership,
                    plan: $plan,
                    actualPrice: $request->request->get('actual_price'),
                    bonusMonths: $request->request->getInt('bonus_months') ?: 0,
                    discountReason: $request->request->get('discount_reason'),
                    notes: $request->request->get('notes')
                );

                $this->addFlash('success', sprintf(
                    'Abbonamento rinnovato con successo! Scadenza: %s',
                    $newMembership->getEndDate()->format('d/m/Y')
                ));

                return $this->redirectToRoute('admin_membership_show', ['id' => $newMembership->getId()]);
            }

            return $this->render('admin/memberships/renew.html.twig', [
                'membership' => $currentMembership,
                'subscription_plans' => $subscriptionPlans,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_memberships');
        }
    }
}
