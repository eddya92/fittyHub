<?php

namespace App\Domain\Membership\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\UseCase\CreateGymMembershipUseCase;
use Symfony\Bundle\SecurityBundle\Security;

final class GymMembershipProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly Security $security,
        private readonly CreateGymMembershipUseCase $createMembershipUseCase
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof GymMembership) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        // Se nuova iscrizione, usa il UseCase per validazioni business
        if (!$data->getId()) {
            $user = $this->security->getUser();
            if (!$user) {
                throw new \RuntimeException('Devi essere autenticato per iscriverti');
            }

            try {
                $membership = $this->createMembershipUseCase->execute(
                    $user,
                    $data->getGym(),
                    $data->getStartDate(),
                    $data->getEndDate(),
                    $data->getNotes()
                );

                return $membership;
            } catch (\DomainException $e) {
                // Re-throw per far gestire ad API Platform
                throw new \RuntimeException($e->getMessage(), 400, $e);
            }
        }

        // Per aggiornamenti, usa il processor standard
        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
        $data->setUpdatedAt(new \DateTimeImmutable());

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
