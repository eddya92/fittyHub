<?php

namespace App\Controller\PT;

use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pt/clients')]
#[IsGranted('ROLE_PT')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'pt_clients')]
    public function index(
        Request $request,
        TrainerRepositoryInterface $trainerRepo,
        PTClientRelationRepositoryInterface $relationRepo,
        MembershipRepositoryInterface $membershipRepo
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
        $view = $request->query->get('view', 'my-clients'); // 'my-clients' o 'gym-clients'

        // Clienti assegnati al PT
        $qb = $relationRepo->createQueryBuilder('r')
            ->leftJoin('r.client', 'c')
            ->where('r.personalTrainer = :trainer')
            ->setParameter('trainer', $trainer)
            ->orderBy('r.startDate', 'DESC');

        // Filtro per status
        if ($status) {
            $qb->andWhere('r.status = :status')
               ->setParameter('status', $status);
        }

        // Filtro ricerca cliente
        if ($search) {
            $qb->andWhere('c.firstName LIKE :search OR c.lastName LIKE :search OR c.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $relations = $qb->getQuery()->getResult();

        // Stats clienti PT
        $stats = [
            'total' => $relationRepo->count(['personalTrainer' => $trainer]),
            'active' => $relationRepo->count(['personalTrainer' => $trainer, 'status' => 'active']),
            'completed' => $relationRepo->count(['personalTrainer' => $trainer, 'status' => 'completed']),
        ];

        // Tutti i clienti della palestra
        $gymMemberships = [];
        $gymStats = [
            'total' => 0,
            'active' => 0,
            'expired' => 0,
        ];

        if ($trainer->getGym()) {
            $gymMembershipsQb = $membershipRepo->createQueryBuilder('m')
                ->leftJoin('m.user', 'u')
                ->where('m.gym = :gym')
                ->setParameter('gym', $trainer->getGym())
                ->orderBy('m.startDate', 'DESC');

            if ($search) {
                $gymMembershipsQb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
                    ->setParameter('search', '%' . $search . '%');
            }

            $gymMemberships = $gymMembershipsQb->getQuery()->getResult();

            $gymStats = [
                'total' => $membershipRepo->count(['gym' => $trainer->getGym()]),
                'active' => $membershipRepo->count(['gym' => $trainer->getGym(), 'status' => 'active']),
                'expired' => $membershipRepo->count(['gym' => $trainer->getGym(), 'status' => 'expired']),
            ];
        }

        return $this->render('pt/clients/index.html.twig', [
            'trainer' => $trainer,
            'relations' => $relations,
            'stats' => $stats,
            'gym_memberships' => $gymMemberships,
            'gym_stats' => $gymStats,
            'current_status' => $status,
            'current_search' => $search,
            'current_view' => $view,
        ]);
    }

    #[Route('/{id}', name: 'pt_client_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        TrainerRepositoryInterface $trainerRepo,
        PTClientRelationRepositoryInterface $relationRepo
    ): Response {
        // Recupera il PT loggato
        $user = $this->getUser();
        $trainer = $trainerRepo->findOneBy(['user' => $user]);

        if (!$trainer) {
            $this->addFlash('error', 'Profilo Personal Trainer non trovato.');
            return $this->redirectToRoute('app_home');
        }

        $relation = $relationRepo->find($id);

        if (!$relation || $relation->getPersonalTrainer() !== $trainer) {
            $this->addFlash('error', 'Cliente non trovato o non hai i permessi per visualizzarlo.');
            return $this->redirectToRoute('pt_clients');
        }

        return $this->render('pt/clients/show.html.twig', [
            'trainer' => $trainer,
            'relation' => $relation,
        ]);
    }
}
