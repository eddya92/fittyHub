<?php

namespace App\Domain\Invitation\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Invitation\Entity\GymPTInvitation;
use App\Domain\Invitation\Repository\InvitationRepositoryInterface;
use App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Use Case: Crea un nuovo invito per Personal Trainer
 */
class CreateInvitation
{
    public function __construct(
        private InvitationRepositoryInterface $invitationRepository,
        private UserRepositoryInterface $userRepository,
        private TrainerRepositoryInterface $trainerRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * @throws \DomainException se esiste già un invito pendente o il trainer è già associato
     */
    public function execute(
        Gym $gym,
        string $trainerEmail,
        ?string $message = null
    ): GymPTInvitation {
        // Controlla se esiste già un invito pendente
        $existingInvitation = $this->invitationRepository->findOneBy([
            'gym' => $gym,
            'trainerEmail' => $trainerEmail,
            'status' => 'pending'
        ]);

        if ($existingInvitation && $existingInvitation->isPending()) {
            throw new \DomainException('Esiste già un invito pendente per questo trainer');
        }

        // Controlla se il trainer è già collegato alla palestra
        $existingUser = $this->userRepository->findOneBy(['email' => $trainerEmail]);
        if ($existingUser) {
            $existingTrainer = $this->trainerRepository->findOneBy([
                'user' => $existingUser,
                'gym' => $gym
            ]);

            if ($existingTrainer) {
                throw new \DomainException('Questo trainer è già associato alla palestra');
            }
        }

        $invitation = new GymPTInvitation();
        $invitation->setGym($gym);
        $invitation->setTrainerEmail($trainerEmail);
        $invitation->setMessage($message);

        if ($existingUser) {
            $invitation->setTrainerUser($existingUser);
        }

        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        $this->sendInvitationEmail($invitation);

        return $invitation;
    }

    private function sendInvitationEmail(GymPTInvitation $invitation): void
    {
        $gym = $invitation->getGym();

        $acceptUrl = $this->urlGenerator->generate(
            'app_invitation_gym_pt_accept',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $declineUrl = $this->urlGenerator->generate(
            'app_invitation_gym_pt_decline',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from('noreply@fittyhub.com')
            ->to($invitation->getTrainerEmail())
            ->subject('Invito Collaborazione da ' . $gym->getName())
            ->htmlTemplate('emails/gym_pt_invitation.html.twig')
            ->context([
                'trainerName' => $invitation->getTrainerUser()
                    ? $invitation->getTrainerUser()->getFirstName()
                    : 'Trainer',
                'gymName' => $gym->getName(),
                'gymAddress' => $gym->getAddress(),
                'gymCity' => $gym->getCity(),
                'gymPhone' => $gym->getPhoneNumber(),
                'gymEmail' => $gym->getEmail(),
                'message' => $invitation->getMessage(),
                'acceptUrl' => $acceptUrl,
                'declineUrl' => $declineUrl,
                'expiresAt' => $invitation->getExpiresAt()
            ]);

        $this->mailer->send($email);
    }
}
