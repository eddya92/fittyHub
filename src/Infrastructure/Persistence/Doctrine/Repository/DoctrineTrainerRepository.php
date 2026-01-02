<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineTrainerRepository extends ServiceEntityRepository implements TrainerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonalTrainer::class);
    }

    public function findWithFilters(?string $search, ?string $specialization): array
    {
        $qb = $this->createQueryBuilder('pt')
            ->innerJoin('pt.user', 'u')
            ->addSelect('u')
            ->where('pt.isActive = :active')
            ->setParameter('active', true);

        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($specialization) {
            $qb->andWhere('pt.specialization LIKE :specialization')
               ->setParameter('specialization', '%' . $specialization . '%');
        }

        return $qb->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
