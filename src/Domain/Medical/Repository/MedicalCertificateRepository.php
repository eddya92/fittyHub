<?php

namespace App\Domain\Medical\Repository;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\User\Entity\User;
use App\Domain\Gym\Entity\Gym;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalCertificate>
 */
class MedicalCertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalCertificate::class);
    }

    /**
     * Find pending certificates for review
     *
     * @return MedicalCertificate[]
     */
    public function findPendingReview(): array
    {
        return $this->createQueryBuilder('mc')
            ->andWhere('mc.status = :status')
            ->setParameter('status', 'pending_review')
            ->orderBy('mc.uploadedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find expiring certificates (within next 30 days)
     *
     * @return MedicalCertificate[]
     */
    public function findExpiringSoon(): array
    {
        $now = new \DateTime();
        $thirtyDaysLater = (new \DateTime())->modify('+30 days');

        return $this->createQueryBuilder('mc')
            ->andWhere('mc.status = :status')
            ->andWhere('mc.expiryDate BETWEEN :now AND :thirtyDays')
            ->setParameter('status', 'approved')
            ->setParameter('now', $now)
            ->setParameter('thirtyDays', $thirtyDaysLater)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find valid certificate for user
     */
    public function findValidCertificateForUser(User $user): ?MedicalCertificate
    {
        return $this->createQueryBuilder('mc')
            ->andWhere('mc.user = :user')
            ->andWhere('mc.status = :status')
            ->andWhere('mc.expiryDate > :now')
            ->setParameter('user', $user)
            ->setParameter('status', 'approved')
            ->setParameter('now', new \DateTime())
            ->orderBy('mc.expiryDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find certificates with filters
     *
     * @return MedicalCertificate[]
     */
    public function findWithFilters(?string $status = null, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->orderBy('c.uploadedAt', 'ASC');

        if ($status) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function save(MedicalCertificate $certificate, bool $flush = false): void
    {
        $this->getEntityManager()->persist($certificate);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
