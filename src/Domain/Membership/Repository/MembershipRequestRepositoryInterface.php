<?php

namespace App\Domain\Membership\Repository;

use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;

/**
 * Repository interface per MembershipRequest
 */
interface MembershipRequestRepositoryInterface
{
    /**
     * Find pending requests for a gym
     *
     * @return MembershipRequest[]
     */
    public function findPendingByGym(Gym $gym): array;

    /**
     * Find existing pending request for user and gym
     */
    public function findPendingRequest(User $user, Gym $gym): ?MembershipRequest;

    /**
     * Save membership request
     */
    public function save(MembershipRequest $request, bool $flush = false): void;
}
