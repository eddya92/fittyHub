<?php

namespace App\Controller\Admin;

use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/trainers')]
class TrainerController extends AbstractController
{
    #[Route('/', name: 'admin_trainers')]
    public function index(
        Request $request,
        PersonalTrainerRepository $trainerRepo
    ): Response {
        // Filtri dalla query string
        $search = $request->query->get('search');
        $specialization = $request->query->get('specialization');

        // Query builder per filtri dinamici
        $qb = $trainerRepo->createQueryBuilder('pt')
            ->leftJoin('pt.user', 'u')
            ->orderBy('u.firstName', 'ASC');

        // Filtro per ricerca (nome utente o email)
        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filtro per specializzazione
        if ($specialization) {
            $qb->andWhere('pt.specializations LIKE :specialization')
               ->setParameter('specialization', '%' . $specialization . '%');
        }

        $trainers = $qb->getQuery()->getResult();

        return $this->render('admin/trainers/index.html.twig', [
            'trainers' => $trainers,
            'current_search' => $search,
            'current_specialization' => $specialization,
        ]);
    }

    #[Route('/{id}', name: 'admin_trainer_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        PersonalTrainerRepository $trainerRepo,
        PTClientRelationRepository $relationRepo
    ): Response {
        $trainer = $trainerRepo->find($id);

        if (!$trainer) {
            $this->addFlash('error', 'Personal Trainer non trovato.');
            return $this->redirectToRoute('admin_trainers');
        }

        // Recupera le relazioni attive con i clienti
        $activeRelations = $relationRepo->findBy(
            ['personalTrainer' => $trainer, 'status' => 'active'],
            ['startDate' => 'DESC']
        );

        // Statistiche
        $stats = [
            'total_clients' => $relationRepo->count(['personalTrainer' => $trainer]),
            'active_clients' => count($activeRelations),
            'completed_relations' => $relationRepo->count(['personalTrainer' => $trainer, 'status' => 'completed']),
        ];

        return $this->render('admin/trainers/show.html.twig', [
            'trainer' => $trainer,
            'active_relations' => $activeRelations,
            'stats' => $stats,
        ]);
    }
}
