<?php

namespace App\Controller\Admin;

use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;
use App\Domain\Membership\UseCase\CreateEnrollment;
use App\Domain\Membership\UseCase\ExpireEnrollment;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/enrollments')]
class EnrollmentController extends AbstractController
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepository,
        private CreateEnrollment $createEnrollment,
        private ExpireEnrollment $expireEnrollment,
        private UserRepositoryInterface $userRepository,
        private GymRepositoryInterface $gymRepository
    ) {}

    #[Route('/', name: 'admin_enrollments')]
    public function index(): Response
    {
        return $this->render('admin/enrollments/index.html.twig', [
            'enrollments' => $this->enrollmentRepository->findAll(),
        ]);
    }

    #[Route('/expiring', name: 'admin_enrollments_expiring')]
    public function expiring(): Response
    {
        // Find enrollments expiring in 30 days
        $expiryDate = new \DateTime('+30 days');
        $enrollments = $this->enrollmentRepository->findBy(
            ['status' => 'active'],
            ['expiryDate' => 'ASC']
        );

        $expiring = array_filter($enrollments, fn($e) => $e->getExpiryDate() <= $expiryDate);

        return $this->render('admin/enrollments/expiring.html.twig', [
            'enrollments' => $expiring,
        ]);
    }

    #[Route('/create', name: 'admin_enrollment_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        try {
            $userId = $request->query->get('user_id');
            $user = $userId ? $this->userRepository->find($userId) : null;

            $users = $this->userRepository->findAll();
            $gyms = $this->gymRepository->findAll();

            if ($request->isMethod('POST')) {
                $userId = $request->request->getInt('user_id');
                $gymId = $request->request->getInt('gym_id');

                $user = $this->userRepository->find($userId);
                $gym = $this->gymRepository->find($gymId);

                if (!$user || !$gym) {
                    throw new \RuntimeException('Utente o palestra non trovati.');
                }

                $this->createEnrollment->execute($user, $gym, [
                    'amount' => $request->request->get('amount'),
                    'payment_date' => $request->request->get('payment_date'),
                    'expiry_date' => $request->request->get('expiry_date'),
                    'notes' => $request->request->get('notes'),
                ]);

                $this->addFlash('success', 'Quota iscrizione creata con successo!');
                return $this->redirectToRoute('admin_enrollments');
            }

            return $this->render('admin/enrollments/create.html.twig', [
                'user' => $user,
                'users' => $users,
                'gyms' => $gyms,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_enrollment_create');
        }
    }

    #[Route('/user/{userId}', name: 'admin_enrollment_user_history')]
    public function userHistory(int $userId): Response
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utente non trovato.');
            return $this->redirectToRoute('admin_enrollments');
        }

        return $this->render('admin/enrollments/user_history.html.twig', [
            'user' => $user,
            'enrollments' => $this->enrollmentRepository->findBy(['user' => $user], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}/expire', name: 'admin_enrollment_expire', methods: ['POST'])]
    public function expire(int $id): Response
    {
        $enrollment = $this->enrollmentRepository->find($id);

        if (!$enrollment) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_enrollments');
        }

        try {
            $this->expireEnrollment->execute($enrollment);

            $this->addFlash('success', 'Quota iscrizione scaduta.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_enrollments');
    }
}