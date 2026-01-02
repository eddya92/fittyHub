<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Implementazione Doctrine del MembershipRepository
 * Infrastructure layer - dettagli tecnici di come salviamo i dati
 */
class DoctrineMembershipRepository extends ServiceEntityRepository implements MembershipRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymMembership::class);
    }

    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('gm')
            ->andWhere('gm.user = :user')
            ->andWhere('gm.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByGym(Gym $gym, User $user): ?GymMembership
    {
        return $this->createQueryBuilder('gm')
            ->andWhere('gm.gym = :gym')
            ->andWhere('gm.user = :user')
            ->andWhere('gm.status = :status')
            ->setParameter('gym', $gym)
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUniqueUsersWithLatestMembership(
        ?string $status,
        ?string $search,
        ?int $gymId,
        int $limit,
        int $offset
    ): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT m.*
            FROM gym_membership m
            INNER JOIN (
                SELECT user_id, MAX(id) as max_id
                FROM gym_membership
                WHERE 1=1
                ' . ($status ? 'AND status = :status' : '') . '
                ' . ($gymId ? 'AND gym_id = :gym' : '') . '
                GROUP BY user_id
            ) latest ON m.id = latest.max_id
            INNER JOIN user u ON m.user_id = u.id
            ' . ($search ? 'WHERE (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)' : '') . '
            ORDER BY u.last_name ASC, u.first_name ASC
            LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset . '
        ';

        $params = [];
        if ($status) {
            $params['status'] = $status;
        }
        if ($search) {
            $params['search'] = '%' . $search . '%';
        }
        if ($gymId) {
            $params['gym'] = $gymId;
        }

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery($params);
        $rows = $result->fetchAllAssociative();

        $memberships = [];
        foreach ($rows as $row) {
            $memberships[] = $this->find($row['id']);
        }

        return $memberships;
    }

    public function countUniqueUsers(?string $status = null): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(DISTINCT m.user)')
            ->where('1=1');

        if ($status) {
            $qb->andWhere('m.id IN (
                SELECT MAX(m2.id)
                FROM ' . GymMembership::class . ' m2
                GROUP BY m2.user
            )')
            ->andWhere('m.status = :status')
            ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
