<?php

namespace App\Controller\Admin;

use App\Application\Service\GymUserService;
use App\Application\Service\SettingsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private SettingsService $settingsService,
        private GymUserService $gymUserService
    ) {}

    #[Route('/', name: 'admin_settings')]
    public function index(): Response
    {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        if (!$gym) {
            $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $settings = $this->settingsService->getOrCreateSettings($gym);

        return $this->render('admin/settings/index.html.twig', [
            'settings' => $settings,
            'gym' => $gym,
        ]);
    }

    #[Route('/update', name: 'admin_settings_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $gym = $this->gymUserService->getPrimaryGym($this->getUser());

        if (!$gym) {
            $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $settings = $this->settingsService->getOrCreateSettings($gym);

        try {
            $this->settingsService->updateSettings($settings, $request->request->all());
            $this->addFlash('success', 'Impostazioni aggiornate con successo.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Errore: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_settings');
    }
}
