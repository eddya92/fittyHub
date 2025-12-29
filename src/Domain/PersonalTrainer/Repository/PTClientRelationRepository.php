<?php

namespace App\Domain\PersonalTrainer\Repository;

use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PTClientRelation>
 */
class PTClientRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PTClientRelation::class);
    }

    /**
     * Find active clients for a PT
     *
     * @return PTClientRelation[]
     */
    public function findActiveByPT(PersonalTrainer $pt): array
    {
        return $this->createQueryBuilder('pcr')
            ->andWhere('pcr.personalTrainer = :pt')
            ->andWhere('pcr.status = :status')
            ->setParameter('pt', $pt)
            ->setParameter('status', 'active')
            ->orderBy('pcr.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active PTs for a client
     *
     * @return PTClientRelation[]
     */
    public function findActiveByClient(User $client): array
    {
        return $this->createQueryBuilder('pcr')
            ->andWhere('pcr.client = :client')
            ->andWhere('pcr.status = :status')
            ->setParameter('client', $client)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if PT-Client relation exists
     */
    public function hasActiveRelation(PersonalTrainer $pt, User $client): bool
    {
        $count = $this->createQueryBuilder('pcr')
            ->select('COUNT(pcr.id)')
            ->andWhere('pcr.personalTrainer = :pt')
            ->andWhere('pcr.client = :client')
            ->andWhere('pcr.status = :status')
            ->setParameter('pt', $pt)
            ->setParameter('client', $client)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
