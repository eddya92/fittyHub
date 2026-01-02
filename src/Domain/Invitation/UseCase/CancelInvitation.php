<?php

namespace App\Domain\Invitation\UseCase;

use App\Domain\Invitation\Entity\GymPTInvitation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Use Case: Cancella un invito in attesa
 */
class CancelInvitation
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws \RuntimeException se l'invito non può essere cancellato
     */
    public function execute(GymPTInvitation $invitation): void
    {
        if ($invitation->getStatus() !== 'pending') {
            throw new \RuntimeException('Puoi cancellare solo inviti in attesa.');
        }

        $invitation->setStatus('expired');
        $this->entityManager->flush();
    }
}
