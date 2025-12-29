<?php

namespace App\Domain\User\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\User\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User || !$data->getPassword()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        // Hash the plain password
        $hashedPassword = $this->passwordHasher->hashPassword(
            $data,
            $data->getPassword()
        );
        $data->setPassword($hashedPassword);

        // Set createdAt and updatedAt if creating new user
        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
        $data->setUpdatedAt(new \DateTimeImmutable());

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
