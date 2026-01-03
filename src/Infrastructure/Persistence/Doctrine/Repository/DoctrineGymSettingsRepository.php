<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Gym\Entity\GymSettings;
use App\Domain\Gym\Repository\GymSettingsRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GymSettings>
 */
class DoctrineGymSettingsRepository extends ServiceEntityRepository implements GymSettingsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GymSettings::class);
    }

    public function save(GymSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GymSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
