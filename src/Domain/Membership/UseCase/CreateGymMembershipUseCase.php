<?php

namespace App\Domain\Membership\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreateGymMembershipUseCase
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GymRepositoryInterface $gymRepository,
        private MedicalCertificateRepositoryInterface $medicalCertificateRepository
    ) {
    }

    public function execute(
        User $user,
        Gym $gym,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        ?string $notes = null
    ): GymMembership {
        // Business Rule 1: Verifica certificato medico valido
        $validCertificate = $this->medicalCertificateRepository->findValidCertificateForUser($user);

        if (!$validCertificate) {
            throw new \DomainException(
                'Devi avere un certificato medico valido per iscriverti in palestra. ' .
                'Carica il tuo certificato prima di procedere.'
            );
        }

        // Business Rule 2: Verifica che non ci sia già un abbonamento attivo per questa palestra
        $existingMembership = $this->entityManager
            ->getRepository(GymMembership::class)
            ->findOneBy([
                'user' => $user,
                'gym' => $gym,
                'status' => 'active'
            ]);

        if ($existingMembership) {
            throw new \DomainException(
                'Hai già un abbonamento attivo per questa palestra. ' .
                'Non puoi avere abbonamenti multipli contemporaneamente.'
            );
        }

        // Business Rule 3: Verifica date validity
        if ($endDate <= $startDate) {
            throw new \DomainException('La data di fine deve essere successiva alla data di inizio');
        }

        // Business Rule 4: Verifica che il certificato copra l'intero periodo
        if ($validCertificate->getExpiryDate() < $endDate) {
            throw new \DomainException(
                'Il tuo certificato medico scade prima della fine dell\'abbonamento. ' .
                'Devi avere un certificato valido per tutta la durata dell\'iscrizione.'
            );
        }

        // Crea l'iscrizione
        $membership = new GymMembership();
        $membership->setUser($user);
        $membership->setGym($gym);
        $membership->setStartDate($startDate);
        $membership->setEndDate($endDate);
        $membership->setStatus('active');
        $membership->setNotes($notes);
        $membership->setMedicalCertificate($validCertificate);

        $this->entityManager->persist($membership);
        $this->entityManager->flush();

        return $membership;
    }
}
