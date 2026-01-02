<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePTClientRelationRepository extends ServiceEntityRepository implements PTClientRelationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PTClientRelation::class);
    }
}
