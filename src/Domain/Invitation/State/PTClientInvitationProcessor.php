<?php

namespace App\Domain\Invitation\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Invitation\Entity\PTClientInvitation;
use App\Domain\Invitation\Service\PTClientInvitationService;
use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class PTClientInvitationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly Security $security,
        private readonly PTClientInvitationService $invitationService,
        private readonly PersonalTrainerRepository $trainerRepository
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof PTClientInvitation) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        // Solo per creazione inviti
        if (!$data->getId()) {
            $user = $this->security->getUser();
            if (!$user) {
                throw new \RuntimeException('Devi essere autenticato');
            }

            // Trova il PersonalTrainer dell'utente loggato
            $trainer = $this->trainerRepository->findOneBy(['user' => $user]);
            if (!$trainer) {
                throw new \RuntimeException('Devi essere un Personal Trainer per inviare inviti');
            }

            try {
                return $this->invitationService->createInvitation(
                    $trainer,
                    $data->getClientEmail(),
                    $data->getMessage()
                );
            } catch (\DomainException $e) {
                throw new \RuntimeException($e->getMessage(), 400, $e);
            }
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
