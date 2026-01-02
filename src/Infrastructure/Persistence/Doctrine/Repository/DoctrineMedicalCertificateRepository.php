<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineMedicalCertificateRepository extends ServiceEntityRepository implements MedicalCertificateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalCertificate::class);
    }

    public function findWithFilters(?string $status, ?string $search): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u');

        if ($status) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('c.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findValidCertificateForUser(User $user): ?MedicalCertificate
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.status = :status')
            ->andWhere('c.expiryDate > :today')
            ->setParameter('user', $user)
            ->setParameter('status', 'approved')
            ->setParameter('today', new \DateTime())
            ->orderBy('c.expiryDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
