<?php

namespace App\Domain\Invitation\Repository;

use App\Domain\Invitation\Entity\GymPTInvitation;
use App\Domain\Gym\Entity\Gym;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GymPTInvitation>
 */
class GymPTInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymPTInvitation::class);
    }

    /**
     * Find invitation by token
     */
    public function findByToken(string $token): ?GymPTInvitation
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * Find pending invitations for a gym
     *
     * @return GymPTInvitation[]
     */
    public function findPendingByGym(Gym $gym): array
    {
        return $this->createQueryBuilder('gpi')
            ->andWhere('gpi.gym = :gym')
            ->andWhere('gpi.status = :status')
            ->andWhere('gpi.expiresAt > :now')
            ->setParameter('gym', $gym)
            ->setParameter('status', 'pending')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('gpi.invitedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find invitations with filters
     *
     * @return GymPTInvitation[]
     */
    public function findWithFilters(?string $status = null, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('i')
            ->orderBy('i.invitedAt', 'DESC');

        if ($status) {
            $qb->andWhere('i.status = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('i.trainerEmail LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
