<?php

namespace App\Application\Service;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymRepository;
use App\Domain\Membership\Repository\MembershipRepository;
use App\Domain\User\Entity\User;

class GymUserService
{
    public function __construct(
        private GymRepository $gymRepository,
        private MembershipRepository $membershipRepository
    ) {}

    /**
     * Ottiene le palestre di cui l'utente è admin
     */
    public function getUserGyms(User $user): array
    {
        return $this->gymRepository->createQueryBuilder('g')
            ->join('g.admins', 'a')
            ->where('a = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Ottiene la prima palestra dell'admin (helper)
     */
    public function getPrimaryGym(User $user): ?Gym
    {
        $gyms = $this->getUserGyms($user);
        return !empty($gyms) ? $gyms[0] : null;
    }

    /**
     * Ottiene tutti gli utenti con membership attiva in una palestra
     */
    public function getActiveMembers(Gym $gym): array
    {
        $memberships = $this->membershipRepository->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->where('m.gym = :gym')
            ->andWhere('m.status = :status')
            ->setParameter('gym', $gym)
            ->setParameter('status', 'active')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn($m) => $m->getUser(), $memberships);
    }
}
