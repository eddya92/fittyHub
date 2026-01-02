<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Invitation\Entity\GymPTInvitation;
use App\Domain\Invitation\Repository\InvitationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineInvitationRepository extends ServiceEntityRepository implements InvitationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymPTInvitation::class);
    }

    public function findWithFilters(?string $status, ?string $search): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.gym', 'g')
            ->addSelect('g');

        if ($status) {
            $qb->andWhere('i.status = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('i.invitedEmail LIKE :search OR g.name LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('i.invitedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
