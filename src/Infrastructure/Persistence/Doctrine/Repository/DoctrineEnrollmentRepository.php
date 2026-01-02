<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Membership\Entity\Enrollment;
use App\Domain\Membership\Repository\EnrollmentRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\Gym\Entity\Gym;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Implementazione Doctrine del EnrollmentRepository
 */
class DoctrineEnrollmentRepository extends ServiceEntityRepository implements EnrollmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrollment::class);
    }

    public function findActiveEnrollment(User $user, Gym $gym): ?Enrollment
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.gym = :gym')
            ->andWhere('e.status = :status')
            ->setParameter('user', $user)
            ->setParameter('gym', $gym)
            ->setParameter('status', 'active')
            ->orderBy('e.paymentDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findExpiringEnrollments(int $days): array
    {
        $today = new \DateTime();
        $futureDate = (new \DateTime())->modify("+{$days} days");

        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->andWhere('e.expiryDate BETWEEN :today AND :futureDate')
            ->setParameter('status', 'active')
            ->setParameter('today', $today)
            ->setParameter('futureDate', $futureDate)
            ->orderBy('e.expiryDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.paymentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
