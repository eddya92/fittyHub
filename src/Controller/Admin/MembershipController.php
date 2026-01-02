<?php

namespace App\Controller\Admin;

use App\Domain\Membership\UseCase\GetMembershipById;
use App\Domain\Membership\UseCase\SearchMemberships;
use App\Domain\Membership\UseCase\CancelMembership;
use App\Domain\Membership\UseCase\RenewMembership;
use App\Domain\Membership\UseCase\GetMembershipStats;
use App\Domain\Membership\UseCase\GetExpiringMemberships;
use App\Domain\Membership\UseCase\ReactivateMembership;
use App\Domain\Membership\UseCase\UpdateMembershipAndUser;
use App\Domain\PersonalTrainer\UseCase\AssignTrainerToClient;
use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;
use App\Domain\Membership\Repository\SubscriptionPlanRepositoryInterface;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller Membership - Architettura DDD
 * Usa Use Cases per orchestrare la business logic
 */
#[Route('/admin/memberships')]
class MembershipController extends AbstractController
{
    public function __construct(
        private GetMembershipById $getMembershipById,
        private SearchMemberships $searchMemberships,
        private CancelMembership $cancelMembership,
        private RenewMembership $renewMembership,
        private GetMembershipStats $getMembershipStats,
        private GetExpiringMemberships $getExpiringMemberships,
        private ReactivateMembership $reactivateMembership,
        private UpdateMembershipAndUser $updateMembershipAndUser,
        private AssignTrainerToClient $assignTrainerToClient,
        private EnrollmentRepositoryInterface $enrollmentRepository,
        private MedicalCertificateRepositoryInterface $certificateRepository,
        private TrainerRepositoryInterface $trainerRepository,
        private PTClientRelationRepositoryInterface $relationRepository,
        private SubscriptionPlanRepositoryInterface $planRepository
    ) {}

    #[Route('/', name: 'admin_memberships')]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $gymId = $request->query->getInt('gym');
        $page = max(1, $request->query->getInt('page', 1));

        // Use Case: cerca abbonamenti
        $result = $this->searchMemberships->execute($status, $search, $gymId, $page);

        // Aggiungi informazioni sulla quota iscrizione
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

        return $this->render('admin/memberships/index.html.twig', [
            'memberships' => $membershipsWithEnrollment,
            'stats' => $this->getMembershipStats->execute(),
            'current_status' => $status,
            'current_search' => $search,
            'current_gym' => $gymId,
            'current_page' => $result['current_page'],
            'total_pages' => $result['total_pages'],
            'total_users' => $result['total_users'],
        ]);
    }

    #[Route('/expiring', name: 'admin_memberships_expiring')]
    public function expiring(): Response
    {
        return $this->render('admin/memberships/expiring.html.twig', [
            'memberships' => $this->getExpiringMemberships->execute(30),
        ]);
    }

    #[Route('/{id}', name: 'admin_membership_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        try {
            // Use Case: ottiene abbonamento
            $membership = $this->getMembershipById->execute($id);

            $certificate = $this->certificateRepository->findOneBy(
                ['user' => $membership->getUser()],
                ['uploadedAt' => 'DESC']
            );

            $trainers = $this->trainerRepository->findBy([
                'gym' => $membership->getGym(),
                'isActive' => true
            ]);

            $activeRelations = $this->relationRepository->findBy([
                'client' => $membership->getUser(),
                'status' => 'active'
            ]);

            $activeEnrollment = $this->enrollmentRepository->findActiveEnrollment(
                $membership->getUser(),
                $membership->getGym()
            );

            return $this->render('admin/memberships/show.html.twig', [
                'membership' => $membership,
                'certificate' => $certificate,
                'trainers' => $trainers,
                'active_relations' => $activeRelations,
                'active_enrollment' => $activeEnrollment,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_memberships');
        }
    }

    #[Route('/{id}/cancel', name: 'admin_membership_cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        try {
            // Use Case: ottiene e cancella abbonamento
            $membership = $this->getMembershipById->execute($id);
            $this->cancelMembership->execute($membership);

            $this->addFlash('success', 'Iscrizione cancellata con successo.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    #[Route('/{id}/reactivate', name: 'admin_membership_reactivate', methods: ['POST'])]
    public function reactivate(int $id): Response
    {
        try {
            $membership = $this->getMembershipById->execute($id);
            $this->reactivateMembership->execute($membership);

            $this->addFlash('success', 'Iscrizione riattivata con successo.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    #[Route('/{id}/edit', name: 'admin_membership_edit')]
    public function edit(int $id, Request $request): Response
    {
        try {
            $membership = $this->getMembershipById->execute($id);

            if ($request->isMethod('POST')) {
                $this->updateMembershipAndUser->execute($membership, $request->request->all());
                $this->addFlash('success', 'Dati aggiornati con successo.');
                return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
            }

            return $this->render('admin/memberships/edit.html.twig', [
                'membership' => $membership,
            ]);
        } catch (\RuntimeException|\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_memberships');
        }
    }

    #[Route('/{id}/assign-pt', name: 'admin_membership_assign_pt', methods: ['POST'])]
    public function assignPT(int $id, Request $request): Response
    {
        try {
            $membership = $this->getMembershipById->execute($id);
            $trainerId = $request->request->getInt('trainer_id');

            if (!$trainerId) {
                throw new \RuntimeException('Seleziona un Personal Trainer.');
            }

            $trainer = $this->trainerRepository->find($trainerId);
            if (!$trainer) {
                throw new \RuntimeException('Personal Trainer non trovato.');
            }

            // Verifica relazione esistente
            $existingRelation = $this->relationRepository->findOneBy([
                'personalTrainer' => $trainer,
                'client' => $membership->getUser(),
                'status' => 'active'
            ]);

            if ($existingRelation) {
                throw new \RuntimeException('Questo cliente è già assegnato a questo PT.');
            }

            $this->assignTrainerToClient->execute(
                $trainer,
                $membership->getUser(),
                $request->request->get('notes')
            );

            $this->addFlash('success', 'Cliente assegnato al Personal Trainer con successo.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    #[Route('/{id}/renew', name: 'admin_membership_renew', methods: ['GET', 'POST'])]
    public function renew(int $id, Request $request): Response
    {
        try {
            // Use Case: ottiene abbonamento corrente
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

                // Use Case: rinnova abbonamento
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
