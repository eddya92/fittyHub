<?php

namespace App\Security\Voter;

use App\Domain\User\Entity\User;
use App\Domain\Workout\Entity\WorkoutPlan;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkoutPlanVoter extends Voter
{
    public const VIEW = 'WORKOUT_PLAN_VIEW';
    public const EDIT = 'WORKOUT_PLAN_EDIT';
    public const DELETE = 'WORKOUT_PLAN_DELETE';

    public function __construct(
        private Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof WorkoutPlan;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var WorkoutPlan $workoutPlan */
        $workoutPlan = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($workoutPlan, $user),
            self::EDIT => $this->canEdit($workoutPlan, $user),
            self::DELETE => $this->canDelete($workoutPlan, $user),
            default => false,
        };
    }

    private function canView(WorkoutPlan $workoutPlan, User $user): bool
    {
        // Admin può vedere tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Il proprietario può vedere il suo piano
        if ($workoutPlan->getUser() === $user) {
            return true;
        }

        // Il PT che ha creato il piano può vederlo
        if ($workoutPlan->getPlanType() === 'trainer_created'
            && $workoutPlan->getPersonalTrainer()
            && $workoutPlan->getPersonalTrainer()->getUser() === $user) {
            return true;
        }

        return false;
    }

    private function canEdit(WorkoutPlan $workoutPlan, User $user): bool
    {
        // Admin può modificare tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Piani creati dal trainer: solo il trainer può modificarli
        if ($workoutPlan->getPlanType() === 'trainer_created') {
            return $workoutPlan->getPersonalTrainer()
                && $workoutPlan->getPersonalTrainer()->getUser() === $user;
        }

        // Piani creati dall'utente: solo l'utente può modificarli
        if ($workoutPlan->getPlanType() === 'user_created') {
            return $workoutPlan->getUser() === $user;
        }

        return false;
    }

    private function canDelete(WorkoutPlan $workoutPlan, User $user): bool
    {
        // Admin può eliminare tutto
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Solo chi ha creato il piano può eliminarlo
        if ($workoutPlan->getPlanType() === 'trainer_created') {
            return $workoutPlan->getPersonalTrainer()
                && $workoutPlan->getPersonalTrainer()->getUser() === $user;
        }

        return $workoutPlan->getUser() === $user;
    }
}
