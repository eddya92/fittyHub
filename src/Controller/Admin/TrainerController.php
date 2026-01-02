<?php

namespace App\Controller\Admin;

use App\Domain\PersonalTrainer\UseCase\GetTrainerById;
use App\Domain\PersonalTrainer\UseCase\SearchTrainers;
use App\Domain\PersonalTrainer\Service\TrainerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/trainers')]
class TrainerController extends AbstractController
{
    public function __construct(
        private GetTrainerById $getTrainerById,
        private SearchTrainers $searchTrainers,
        private TrainerService $trainerService
    ) {}

    #[Route('/', name: 'admin_trainers')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search');
        $specialization = $request->query->get('specialization');

        return $this->render('admin/trainers/index.html.twig', [
            'trainers' => $this->searchTrainers->execute($search, $specialization),
            'current_search' => $search,
            'current_specialization' => $specialization,
        ]);
    }

    #[Route('/{id}', name: 'admin_trainer_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        try {
            $trainer = $this->getTrainerById->execute($id);

            return $this->render('admin/trainers/show.html.twig', [
                'trainer' => $trainer,
                'active_relations' => $this->trainerService->getActiveRelations($trainer),
                'stats' => $this->trainerService->getTrainerStats($trainer),
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_trainers');
        }
    }
}
