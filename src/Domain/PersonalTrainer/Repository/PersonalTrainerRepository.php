<?php

namespace App\Domain\PersonalTrainer\Repository;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\Gym\Entity\Gym;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonalTrainer>
 */
class PersonalTrainerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonalTrainer::class);
    }

    /**
     * Find active external PTs (freelance)
     *
     * @return PersonalTrainer[]
     */
    public function findExternalPTs(): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.isInternal = :isInternal')
            ->andWhere('pt.isActive = :isActive')
            ->andWhere('pt.isAvailableForNewClients = :available')
            ->setParameter('isInternal', false)
            ->setParameter('isActive', true)
            ->setParameter('available', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find internal PTs of a specific gym
     *
     * @return PersonalTrainer[]
     */
    public function findByGym(Gym $gym): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.gym = :gym')
            ->andWhere('pt.isInternal = :isInternal')
            ->andWhere('pt.isActive = :isActive')
            ->setParameter('gym', $gym)
            ->setParameter('isInternal', true)
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find available PTs for new clients
     *
     * @return PersonalTrainer[]
     */
    public function findAvailablePTs(): array
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.isActive = :isActive')
            ->andWhere('pt.isAvailableForNewClients = :available')
            ->setParameter('isActive', true)
            ->setParameter('available', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trova trainer con filtri dinamici
     */
    public function findWithFilters(?string $search = null, ?string $specialization = null): array
    {
        $qb = $this->createQueryBuilder('pt')
            ->leftJoin('pt.user', 'u')
            ->orderBy('u.firstName', 'ASC');

        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($specialization) {
            $qb->andWhere('pt.specializations LIKE :specialization')
               ->setParameter('specialization', '%' . $specialization . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
