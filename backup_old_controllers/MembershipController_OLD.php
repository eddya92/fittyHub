<?php

namespace App\Controller\Admin;

use App\Domain\Membership\Repository\GymMembershipRepository;
use App\Domain\User\Repository\UserRepository;
use App\Domain\Gym\Repository\GymRepository;
use App\Domain\Medical\Repository\MedicalCertificateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/memberships')]
class MembershipController extends AbstractController
{
    #[Route('/', name: 'admin_memberships')]
    public function index(
        Request $request,
        GymMembershipRepository $membershipRepo
    ): Response {
        // Filtri dalla query string
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $gym = $request->query->get('gym');

        // Query builder per filtri dinamici
        $qb = $membershipRepo->createQueryBuilder('m')
            ->leftJoin('m.user', 'u')
            ->leftJoin('m.gym', 'g')
            ->orderBy('m.createdAt', 'DESC');

        // Filtro per status
        if ($status) {
            $qb->andWhere('m.status = :status')
               ->setParameter('status', $status);
        }

        // Filtro per ricerca (nome utente o email)
        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filtro per palestra
        if ($gym) {
            $qb->andWhere('g.id = :gym')
               ->setParameter('gym', $gym);
        }

        $memberships = $qb->getQuery()->getResult();

        // Stats per la vista
        $stats = [
            'total' => $membershipRepo->count([]),
            'active' => $membershipRepo->count(['status' => 'active']),
            'expired' => $membershipRepo->count(['status' => 'expired']),
            'cancelled' => $membershipRepo->count(['status' => 'cancelled']),
        ];

        return $this->render('admin/memberships/index.html.twig', [
            'memberships' => $memberships,
            'stats' => $stats,
            'current_status' => $status,
            'current_search' => $search,
            'current_gym' => $gym,
        ]);
    }

    #[Route('/{id}', name: 'admin_membership_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        GymMembershipRepository $membershipRepo,
        MedicalCertificateRepository $certificateRepo
    ): Response {
        $membership = $membershipRepo->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        // Cerca il certificato medico dell'utente
        $certificate = $certificateRepo->findOneBy(
            ['user' => $membership->getUser()],
            ['uploadedAt' => 'DESC']
        );

        return $this->render('admin/memberships/show.html.twig', [
            'membership' => $membership,
            'certificate' => $certificate,
        ]);
    }

    #[Route('/{id}/cancel', name: 'admin_membership_cancel', methods: ['POST'])]
    public function cancel(
        int $id,
        GymMembershipRepository $membershipRepo
    ): Response {
        $membership = $membershipRepo->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        if ($membership->getStatus() !== 'active') {
            $this->addFlash('error', 'Puoi cancellare solo iscrizioni attive.');
            return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
        }

        $membership->setStatus('cancelled');
        $membershipRepo->save($membership, true);

        $this->addFlash('success', 'Iscrizione cancellata con successo.');

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    #[Route('/{id}/reactivate', name: 'admin_membership_reactivate', methods: ['POST'])]
    public function reactivate(
        int $id,
        GymMembershipRepository $membershipRepo
    ): Response {
        $membership = $membershipRepo->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        if ($membership->getStatus() !== 'cancelled' && $membership->getStatus() !== 'expired') {
            $this->addFlash('error', 'Puoi riattivare solo iscrizioni cancellate o scadute.');
            return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
        }

        $membership->setStatus('active');
        $membershipRepo->save($membership, true);

        $this->addFlash('success', 'Iscrizione riattivata con successo.');

        return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
    }

    #[Route('/{id}/edit', name: 'admin_membership_edit')]
    public function edit(
        int $id,
        Request $request,
        GymMembershipRepository $membershipRepo,
        UserRepository $userRepo
    ): Response {
        $membership = $membershipRepo->find($id);

        if (!$membership) {
            $this->addFlash('error', 'Iscrizione non trovata.');
            return $this->redirectToRoute('admin_memberships');
        }

        if ($request->isMethod('POST')) {
            $user = $membership->getUser();

            // Aggiorna dati utente
            if ($firstName = $request->request->get('first_name')) {
                $user->setFirstName($firstName);
            }
            if ($lastName = $request->request->get('last_name')) {
                $user->setLastName($lastName);
            }
            if ($email = $request->request->get('email')) {
                $user->setEmail($email);
            }
            if ($phone = $request->request->get('phone_number')) {
                $user->setPhoneNumber($phone);
            }
            if ($dateOfBirth = $request->request->get('date_of_birth')) {
                $user->setDateOfBirth(new \DateTime($dateOfBirth));
            }
            if ($gender = $request->request->get('gender')) {
                $user->setGender($gender);
            }

            // Aggiorna dati iscrizione
            if ($startDate = $request->request->get('start_date')) {
                $membership->setStartDate(new \DateTime($startDate));
            }
            if ($endDate = $request->request->get('end_date')) {
                $membership->setEndDate(new \DateTime($endDate));
            }
            if ($notes = $request->request->get('notes')) {
                $membership->setNotes($notes);
            }
            if ($status = $request->request->get('status')) {
                $membership->setStatus($status);
            }

            $userRepo->save($user, true);
            $membershipRepo->save($membership, true);

            $this->addFlash('success', 'Dati aggiornati con successo.');
            return $this->redirectToRoute('admin_membership_show', ['id' => $id]);
        }

        return $this->render('admin/memberships/edit.html.twig', [
            'membership' => $membership,
        ]);
    }
}
