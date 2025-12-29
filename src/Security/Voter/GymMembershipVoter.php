<?php

namespace App\Security\Voter;

use App\Domain\Membership\Entity\GymMembership;
use App\Domain\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GymMembershipVoter extends Voter
{
    public const VIEW = 'GYM_MEMBERSHIP_VIEW';
    public const EDIT = 'GYM_MEMBERSHIP_EDIT';
    public const CANCEL = 'GYM_MEMBERSHIP_CANCEL';

    public function __construct(
        private Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::CANCEL])
            && $subject instanceof GymMembership;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var GymMembership $membership */
        $membership = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($membership, $user),
            self::EDIT => $this->canEdit($membership, $user),
            self::CANCEL => $this->canCancel($membership, $user),
            default => false,
        };
    }

    private function canView(GymMembership $membership, User $user): bool
    {
        // Admin può vedere tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // L'utente può vedere la propria iscrizione
        if ($membership->getUser() === $user) {
            return true;
        }

        // L'admin della palestra può vedere tutte le iscrizioni
        if ($this->security->isGranted('ROLE_GYM_ADMIN')) {
            // TODO: Verificare che l'utente sia admin della palestra specifica
            return true;
        }

        return false;
    }

    private function canEdit(GymMembership $membership, User $user): bool
    {
        // Admin può modificare tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Solo l'admin della palestra può modificare iscrizioni
        if ($this->security->isGranted('ROLE_GYM_ADMIN')) {
            // TODO: Verificare che l'utente sia admin della palestra specifica
            return true;
        }

        return false;
    }

    private function canCancel(GymMembership $membership, User $user): bool
    {
        // Admin può cancellare tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // L'utente può cancellare la propria iscrizione
        if ($membership->getUser() === $user) {
            return true;
        }

        // L'admin della palestra può sospendere/cancellare iscrizioni
        if ($this->security->isGranted('ROLE_GYM_ADMIN')) {
            // TODO: Verificare che l'utente sia admin della palestra specifica
            return true;
        }

        return false;
    }
}
