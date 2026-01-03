<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePaymentRepository extends ServiceEntityRepository implements PaymentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.paymentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByGym(Gym $gym): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.gym = :gym')
            ->setParameter('gym', $gym)
            ->orderBy('p.paymentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(
        ?Gym $gym = null,
        ?User $user = null,
        ?string $paymentType = null,
        ?string $paymentMethod = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.membership', 'm')
            ->leftJoin('p.courseEnrollment', 'ce');

        if ($gym) {
            $qb->andWhere('p.gym = :gym')
               ->setParameter('gym', $gym);
        }

        if ($user) {
            $qb->andWhere('p.user = :user')
               ->setParameter('user', $user);
        }

        if ($paymentType) {
            $qb->andWhere('p.paymentType = :paymentType')
               ->setParameter('paymentType', $paymentType);
        }

        if ($paymentMethod) {
            $qb->andWhere('p.paymentMethod = :paymentMethod')
               ->setParameter('paymentMethod', $paymentMethod);
        }

        if ($startDate) {
            $qb->andWhere('p.paymentDate >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('p.paymentDate <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        return $qb->orderBy('p.paymentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalRevenue(
        Gym $gym,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): float {
        $qb = $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->where('p.gym = :gym')
            ->setParameter('gym', $gym);

        if ($startDate) {
            $qb->andWhere('p.paymentDate >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('p.paymentDate <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        return (float)($qb->getQuery()->getSingleScalarResult() ?? 0);
    }

    public function getRevenueByType(
        Gym $gym,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->select('p.paymentType', 'SUM(p.amount) as total', 'COUNT(p.id) as count')
            ->where('p.gym = :gym')
            ->setParameter('gym', $gym)
            ->groupBy('p.paymentType');

        if ($startDate) {
            $qb->andWhere('p.paymentDate >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('p.paymentDate <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Payment $payment, bool $flush = false): void
    {
        $this->getEntityManager()->persist($payment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Payment $payment, bool $flush = false): void
    {
        $this->getEntityManager()->remove($payment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
