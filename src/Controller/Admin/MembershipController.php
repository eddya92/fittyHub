<?php

namespace App\Controller\Admin;

use App\Domain\Membership\UseCase\CancelMembership;
use App\Domain\Membership\UseCase\RenewMembership;
use App\Domain\Membership\UseCase\ReactivateMembership;
use App\Domain\Membership\UseCase\UpdateMembershipAndUser;
use App\Domain\PersonalTrainer\UseCase\AssignTrainerToClient;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
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
        private MembershipRepositoryInterface $membershipRepository,
        private CancelMembership $cancelMembership,
        private RenewMembership $renewMembership,
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

        $memberships = $status
            ? $this->membershipRepository->findBy(['status' => $status])
            : $this->membershipRepository->findAll();

        // Aggiungi informazioni sulla quota iscrizione
        $membershipsWithEnrollment = [];
        foreach ($memberships as $membership) {
            $enrollment = $this->enrollmentRepository->findActiveEnrollment(
                $membership->getUser(),
                $membership->getGym()
            );

            $membershipsWithEnrollment[] = [
                'membership' => $membership,
                'enrollment' => $enrollment,
            ];
        }

        $allMemberships = $this->membershipRepository->findAll();
        $stats = [
            'total' => count($allMemberships),
            'active' => count(array_filter($allMemberships, fn($m) => $m->getStatus() === 'active')),
            'expired' => count(array_filter($allMemberships, fn($m) => $m->getStatus() === 'expired')),
            'cancelled' => count(array_filter($allMemberships, fn($m) => $m->getStatus() === 'cancelled')),
        ];

        return $this->render('admin/memberships/index.html.twig', [
            'memberships' => $membershipsWithEnrollment,
            'stats' => $stats,
            'current_status' => $status,
            'current_search' => $search,
            'current_gym' => $gymId,
            'current_page' => 1,
            'total_pages' => 1,
            'total_users' => count($membershipsWithEnrollment),
        ]);
    }

    #[Route('/expiring', name: 'admin_memberships_expiring')]
    public function expiring(): Response
    {
        $expiryDate = new \DateTime('+30 days');
        $activeMemberships = $this->membershipRepository->findBy(['status' => 'active']);
        $expiring = array_filter($activeMemberships, fn($m) => $m->getEndDate() <= $expiryDate);

        return $this->render('admin/memberships/expiring.html.twig', [
            'memberships' => $expiring,
        ]);
    }

    #[Route('/{id}', name: 'admin_membership_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Abbonamento non trovato.');
            return $this->redirectToRoute('admin_memberships');
        }

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

        $membershipHistory = $this->membershipRepository->findBy(
            ['user' => $membership->getUser(), 'gym' => $membership->getGym()],
            ['startDate' => 'DESC']
        );

        return $this->render('admin/memberships/show.html.twig', [
            'membership' => $membership,
            'certificate' => $certificate,
            'trainers' => $trainers,
            'active_relations' => $activeRelations,
            'active_enrollment' => $activeEnrollment,
            'membership_history' => $membershipHistory,
        ]);
    }

    #[Route('/{id}/cancel', name: 'admin_membership_cancel', methods: ['POST'])]
    public function cancel(int $id): Response
    {
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Abbonamento non trovato.');
            return $this->redirectToRoute('admin_memberships');
        }

        try {
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
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Abbonamento non trovato.');
            return $this->redirectToRoute('admin_memberships');
        }

        try {
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
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Abbonamento non trovato.');
            return $this->redirectToRoute('admin_memberships');
        }

        try {
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
        $membership = $this->membershipRepository->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Abbonamento non trovato.');
            return $this->redirectToRoute('admin_memberships');
        }

        try {
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
        $currentMembership = $this->membershipRepository->find($id);

        if (!$currentMembership) {
            $this->addFlash('error', 'Abbonamento non trovato.');
            return $this->redirectToRoute('admin_memberships');
        }

        try {
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
