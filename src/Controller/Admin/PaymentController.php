<?php

namespace App\Controller\Admin;

use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\Payment\UseCase\GetPaymentHistoryUseCase;
use App\Domain\Payment\UseCase\RecordPaymentUseCase;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/payments')]
class PaymentController extends AbstractController
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private RecordPaymentUseCase $recordPaymentUseCase,
        private GetPaymentHistoryUseCase $getPaymentHistoryUseCase,
        private UserRepositoryInterface $userRepository,
        private GymRepositoryInterface $gymRepository,
        private MembershipRepositoryInterface $membershipRepository,
        private CourseEnrollmentRepositoryInterface $courseEnrollmentRepository
    ) {
    }

    #[Route('/', name: 'admin_payments')]
    public function index(Request $request): Response
    {
        $gymId = $request->query->getInt('gym');
        $userId = $request->query->getInt('user');
        $paymentType = $request->query->get('payment_type');
        $paymentMethod = $request->query->get('payment_method');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        $gym = $gymId ? $this->gymRepository->find($gymId) : null;
        $user = $userId ? $this->userRepository->find($userId) : null;

        $startDateObj = $startDate ? new \DateTime($startDate) : null;
        $endDateObj = $endDate ? new \DateTime($endDate) : null;

        $payments = $this->getPaymentHistoryUseCase->execute(
            $gym,
            $user,
            $paymentType,
            $paymentMethod,
            $startDateObj,
            $endDateObj
        );

        // Calculate totals
        $totalAmount = array_reduce($payments, function($sum, $payment) {
            return $sum + floatval($payment->getAmount());
        }, 0);

        $stats = [
            'total_payments' => count($payments),
            'total_amount' => $totalAmount,
        ];

        // Get available gyms and users for filters
        $gyms = $this->gymRepository->findAll();
        $users = $this->userRepository->findAll();

        return $this->render('admin/payments/index.html.twig', [
            'payments' => $payments,
            'stats' => $stats,
            'gyms' => $gyms,
            'users' => $users,
            'current_gym' => $gym,
            'current_user' => $user,
            'current_payment_type' => $paymentType,
            'current_payment_method' => $paymentMethod,
            'current_start_date' => $startDate,
            'current_end_date' => $endDate,
        ]);
    }

    #[Route('/new', name: 'admin_payment_new')]
    public function new(Request $request): Response
    {
        $userId = $request->query->getInt('user');
        $membershipId = $request->query->getInt('membership');
        $enrollmentId = $request->query->getInt('enrollment');

        $user = $userId ? $this->userRepository->find($userId) : null;
        $membership = $membershipId ? $this->membershipRepository->find($membershipId) : null;
        $enrollment = $enrollmentId ? $this->courseEnrollmentRepository->find($enrollmentId) : null;

        if ($request->isMethod('POST')) {
            try {
                $userId = $request->request->getInt('user_id');
                $gymId = $request->request->getInt('gym_id');
                $amount = $request->request->get('amount');
                $paymentMethod = $request->request->get('payment_method');
                $paymentType = $request->request->get('payment_type');
                $paymentDate = new \DateTime($request->request->get('payment_date'));
                $notes = $request->request->get('notes');
                $transactionReference = $request->request->get('transaction_reference');

                $user = $this->userRepository->find($userId);
                $gym = $this->gymRepository->find($gymId);

                if (!$user || !$gym) {
                    $this->addFlash('error', 'Utente o palestra non trovati.');
                    return $this->redirectToRoute('admin_payment_new');
                }

                $membership = null;
                $enrollment = null;

                if ($paymentType === 'membership') {
                    $membershipId = $request->request->getInt('membership_id');
                    $membership = $this->membershipRepository->find($membershipId);
                }

                if ($paymentType === 'course_enrollment') {
                    $enrollmentId = $request->request->getInt('enrollment_id');
                    $enrollment = $this->courseEnrollmentRepository->find($enrollmentId);
                }

                $payment = $this->recordPaymentUseCase->execute(
                    $user,
                    $gym,
                    $amount,
                    $paymentMethod,
                    $paymentType,
                    $paymentDate,
                    $this->getUser(),
                    $membership,
                    $enrollment,
                    $notes,
                    $transactionReference
                );

                $this->addFlash('success', 'Pagamento registrato con successo.');
                return $this->redirectToRoute('admin_payment_show', ['id' => $payment->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore: ' . $e->getMessage());
            }
        }

        $gyms = $this->gymRepository->findAll();
        $users = $this->userRepository->findAll();

        return $this->render('admin/payments/new.html.twig', [
            'gyms' => $gyms,
            'users' => $users,
            'preselected_user' => $user,
            'preselected_membership' => $membership,
            'preselected_enrollment' => $enrollment,
        ]);
    }

    #[Route('/{id}', name: 'admin_payment_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            $this->addFlash('error', 'Pagamento non trovato.');
            return $this->redirectToRoute('admin_payments');
        }

        return $this->render('admin/payments/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_payment_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            $this->addFlash('error', 'Pagamento non trovato.');
            return $this->redirectToRoute('admin_payments');
        }

        try {
            $this->paymentRepository->remove($payment, true);
            $this->addFlash('success', 'Pagamento eliminato con successo.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Errore durante l\'eliminazione: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_payments');
    }

    #[Route('/reports', name: 'admin_payment_reports')]
    public function reports(Request $request): Response
    {
        $gymId = $request->query->getInt('gym');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        $gym = $gymId ? $this->gymRepository->find($gymId) : $this->gymRepository->findAll()[0] ?? null;

        if (!$gym) {
            $this->addFlash('error', 'Nessuna palestra disponibile.');
            return $this->redirectToRoute('admin_payments');
        }

        $startDateObj = $startDate ? new \DateTime($startDate) : new \DateTime('first day of this month');
        $endDateObj = $endDate ? new \DateTime($endDate) : new \DateTime('last day of this month');

        $totalRevenue = $this->getPaymentHistoryUseCase->getTotalRevenue(
            $gym,
            $startDateObj,
            $endDateObj
        );

        $revenueByType = $this->getPaymentHistoryUseCase->getRevenueByType(
            $gym,
            $startDateObj,
            $endDateObj
        );

        $gyms = $this->gymRepository->findAll();

        return $this->render('admin/payments/reports.html.twig', [
            'gyms' => $gyms,
            'current_gym' => $gym,
            'start_date' => $startDateObj,
            'end_date' => $endDateObj,
            'total_revenue' => $totalRevenue,
            'revenue_by_type' => $revenueByType,
        ]);
    }
}
