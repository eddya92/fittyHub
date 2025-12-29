<?php

namespace App\Domain\Invitation\Repository;

use App\Domain\Invitation\Entity\PTClientInvitation;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PTClientInvitation>
 */
class PTClientInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PTClientInvitation::class);
    }

    /**
     * Find invitation by token
     */
    public function findByToken(string $token): ?PTClientInvitation
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * Find pending invitations for a PT
     *
     * @return PTClientInvitation[]
     */
    public function findPendingByPT(PersonalTrainer $pt): array
    {
        return $this->createQueryBuilder('pci')
            ->andWhere('pci.personalTrainer = :pt')
            ->andWhere('pci.status = :status')
            ->andWhere('pci.expiresAt > :now')
            ->setParameter('pt', $pt)
            ->setParameter('status', 'pending')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('pci.invitedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
