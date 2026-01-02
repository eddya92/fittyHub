<?php

namespace App\Domain\Invitation\UseCase;

use App\Domain\Invitation\Entity\GymPTInvitation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Use Case: Reinvia un invito creandone uno nuovo
 */
class ResendInvitation
{
    public function __construct(
        private CreateInvitation $createInvitation,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws \RuntimeException se l'invito non può essere reinviato
     */
    public function execute(GymPTInvitation $oldInvitation): GymPTInvitation
    {
        if ($oldInvitation->getStatus() !== 'pending' && $oldInvitation->getStatus() !== 'expired') {
            throw new \RuntimeException('Puoi reinviare solo inviti in attesa o scaduti.');
        }

        // Crea nuovo invito
        $newInvitation = $this->createInvitation->execute(
            $oldInvitation->getGym(),
            $oldInvitation->getInvitedEmail() ?? $oldInvitation->getTrainerEmail(),
            'Reinvio: ' . ($oldInvitation->getMessage() ?? '')
        );

        // Marca il vecchio come scaduto
        $oldInvitation->setStatus('expired');
        $this->entityManager->flush();

        return $newInvitation;
    }
}
