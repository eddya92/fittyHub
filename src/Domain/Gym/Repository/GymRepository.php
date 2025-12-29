<?php

namespace App\Domain\Gym\Repository;

use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gym>
 */
class GymRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gym::class);
    }

    /**
     * Save gym entity
     */
    public function save(Gym $gym, bool $flush = false): void
    {
        $this->getEntityManager()->persist($gym);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove gym entity
     */
    public function remove(Gym $gym, bool $flush = false): void
    {
        $this->getEntityManager()->remove($gym);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find active gyms
     *
     * @return Gym[]
     */
    public function findActiveGyms(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find gyms managed by a specific admin
     *
     * @return Gym[]
     */
    public function findByAdmin(User $admin): array
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.admins', 'a')
            ->andWhere('a = :admin')
            ->andWhere('g.isActive = :active')
            ->setParameter('admin', $admin)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find gyms by city
     *
     * @return Gym[]
     */
    public function findByCity(string $city): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.city = :city')
            ->andWhere('g.isActive = :active')
            ->setParameter('city', $city)
            ->setParameter('active', true)
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
