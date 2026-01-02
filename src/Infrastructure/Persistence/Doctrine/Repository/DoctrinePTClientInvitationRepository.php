<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Invitation\Entity\PTClientInvitation;
use App\Domain\Invitation\Repository\PTClientInvitationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePTClientInvitationRepository extends ServiceEntityRepository implements PTClientInvitationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PTClientInvitation::class);
    }
}
