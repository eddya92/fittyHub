<?php

namespace App\Security\Voter;

use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PTClientRelationVoter extends Voter
{
    public const VIEW = 'PT_CLIENT_RELATION_VIEW';
    public const MANAGE = 'PT_CLIENT_RELATION_MANAGE';
    public const TERMINATE = 'PT_CLIENT_RELATION_TERMINATE';

    public function __construct(
        private Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::MANAGE, self::TERMINATE])
            && $subject instanceof PTClientRelation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var PTClientRelation $relation */
        $relation = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($relation, $user),
            self::MANAGE => $this->canManage($relation, $user),
            self::TERMINATE => $this->canTerminate($relation, $user),
            default => false,
        };
    }

    private function canView(PTClientRelation $relation, User $user): bool
    {
        // Admin può vedere tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Il PT può vedere le sue relazioni
        if ($relation->getPersonalTrainer()->getUser() === $user) {
            return true;
        }

        // Il cliente può vedere le sue relazioni
        if ($relation->getClient() === $user) {
            return true;
        }

        // L'admin della palestra può vedere le relazioni dei suoi PT
        if ($this->security->isGranted('ROLE_GYM_ADMIN')) {
            $gym = $relation->getPersonalTrainer()->getGym();
            // TODO: Verificare che l'utente sia admin di questa palestra
            return true;
        }

        return false;
    }

    private function canManage(PTClientRelation $relation, User $user): bool
    {
        // Admin può gestire tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Solo il PT può gestire la relazione (modificare note, piani, ecc.)
        return $relation->getPersonalTrainer()->getUser() === $user;
    }

    private function canTerminate(PTClientRelation $relation, User $user): bool
    {
        // Admin può terminare tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Sia il PT che il cliente possono terminare la relazione
        return $relation->getPersonalTrainer()->getUser() === $user
            || $relation->getClient() === $user;
    }
}
