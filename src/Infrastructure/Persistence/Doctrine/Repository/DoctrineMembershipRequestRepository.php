<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Membership\Repository\MembershipRequestRepositoryInterface;
use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineMembershipRequestRepository extends ServiceEntityRepository implements MembershipRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipRequest::class);
    }

    public function findPendingByGym(Gym $gym): array
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.gym = :gym')
            ->andWhere('mr.status = :status')
            ->setParameter('gym', $gym)
            ->setParameter('status', 'pending')
            ->orderBy('mr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingRequest(User $user, Gym $gym): ?MembershipRequest
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.user = :user')
            ->andWhere('mr.gym = :gym')
            ->andWhere('mr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('gym', $gym)
            ->setParameter('status', 'pending')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(MembershipRequest $request, bool $flush = false): void
    {
        $this->getEntityManager()->persist($request);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
