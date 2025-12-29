<?php

namespace App\Controller\Admin;

use App\Application\Service\TrainerService;
use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/trainers')]
class TrainerController extends AbstractController
{
    public function __construct(
        private TrainerService $trainerService,
        private PersonalTrainerRepository $trainerRepository
    ) {}

    #[Route('/', name: 'admin_trainers')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search');
        $specialization = $request->query->get('specialization');

        $trainers = $this->trainerRepository->findWithFilters($search, $specialization);

        return $this->render('admin/trainers/index.html.twig', [
            'trainers' => $trainers,
            'current_search' => $search,
            'current_specialization' => $specialization,
        ]);
    }

    #[Route('/{id}', name: 'admin_trainer_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $trainer = $this->trainerRepository->find($id);

        if (!$trainer) {
            $this->addFlash('error', 'Personal Trainer non trovato.');
            return $this->redirectToRoute('admin_trainers');
        }

        $activeRelations = $this->trainerService->getActiveRelations($trainer);
        $stats = $this->trainerService->getTrainerStats($trainer);

        return $this->render('admin/trainers/show.html.twig', [
            'trainer' => $trainer,
            'active_relations' => $activeRelations,
            'stats' => $stats,
        ]);
    }
}
