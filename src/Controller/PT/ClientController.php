<?php

namespace App\Controller\PT;

use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepository;
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
        PersonalTrainerRepository $trainerRepo,
        PTClientRelationRepository $relationRepo
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

        // Stats
        $stats = [
            'total' => $relationRepo->count(['personalTrainer' => $trainer]),
            'active' => $relationRepo->count(['personalTrainer' => $trainer, 'status' => 'active']),
            'completed' => $relationRepo->count(['personalTrainer' => $trainer, 'status' => 'completed']),
        ];

        return $this->render('pt/clients/index.html.twig', [
            'trainer' => $trainer,
            'relations' => $relations,
            'stats' => $stats,
            'current_status' => $status,
            'current_search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'pt_client_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        PersonalTrainerRepository $trainerRepo,
        PTClientRelationRepository $relationRepo
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
