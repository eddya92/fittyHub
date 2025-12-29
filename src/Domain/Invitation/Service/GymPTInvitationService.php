<?php

namespace App\Domain\Invitation\Service;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Invitation\Entity\GymPTInvitation;
use App\Domain\Invitation\Repository\GymPTInvitationRepository;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Repository\PersonalTrainerRepository;
use App\Domain\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GymPTInvitationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GymPTInvitationRepository $invitationRepository,
        private UserRepository $userRepository,
        private PersonalTrainerRepository $trainerRepository,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function createInvitation(
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

    public function acceptInvitation(string $token): PersonalTrainer
    {
        $invitation = $this->invitationRepository->findOneBy(['token' => $token]);

        if (!$invitation) {
            throw new \DomainException('Invito non trovato');
        }

        if (!$invitation->isPending()) {
            throw new \DomainException('Questo invito non è più valido');
        }

        if ($invitation->isExpired()) {
            throw new \DomainException('Questo invito è scaduto');
        }

        $user = $invitation->getTrainerUser();
        if (!$user) {
            throw new \DomainException('Devi prima registrarti per accettare questo invito');
        }

        // Aggiungi ruolo PT all'utente se non ce l'ha
        if (!in_array('ROLE_PT', $user->getRoles())) {
            $user->setRoles(array_merge($user->getRoles(), ['ROLE_PT']));
        }

        // Crea il Personal Trainer associato alla palestra
        $trainer = new PersonalTrainer();
        $trainer->setUser($user);
        $trainer->setGym($invitation->getGym());
        $trainer->setSpecialization($invitation->getSpecialization());
        $trainer->setBio($invitation->getBio());

        $this->entityManager->persist($trainer);

        // Aggiorna stato invito
        $invitation->setStatus('accepted');
        $invitation->setRespondedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $trainer;
    }

    public function declineInvitation(string $token): void
    {
        $invitation = $this->invitationRepository->findOneBy(['token' => $token]);

        if (!$invitation) {
            throw new \DomainException('Invito non trovato');
        }

        if (!$invitation->isPending()) {
            throw new \DomainException('Questo invito non è più valido');
        }

        $invitation->setStatus('declined');
        $invitation->setRespondedAt(new \DateTimeImmutable());

        $this->entityManager->flush();
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

    /**
     * Reinvia un invito creandone uno nuovo e marcando il vecchio come scaduto
     */
    public function resendInvitation(GymPTInvitation $oldInvitation): GymPTInvitation
    {
        if ($oldInvitation->getStatus() !== 'pending' && $oldInvitation->getStatus() !== 'expired') {
            throw new \RuntimeException('Puoi reinviare solo inviti in attesa o scaduti.');
        }

        // Crea nuovo invito
        $newInvitation = $this->createInvitation(
            $oldInvitation->getGym(),
            $oldInvitation->getInvitedEmail() ?? $oldInvitation->getTrainerEmail(),
            'Reinvio: ' . ($oldInvitation->getMessage() ?? '')
        );

        // Marca il vecchio come scaduto
        $oldInvitation->setStatus('expired');
        $this->entityManager->flush();

        return $newInvitation;
    }

    /**
     * Cancella un invito in attesa
     */
    public function cancelInvitation(GymPTInvitation $invitation): void
    {
        if ($invitation->getStatus() !== 'pending') {
            throw new \RuntimeException('Puoi cancellare solo inviti in attesa.');
        }

        $invitation->setStatus('expired');
        $this->entityManager->flush();
    }

    /**
     * Ottiene statistiche inviti
     */
    public function getStats(): array
    {
        return [
            'pending' => $this->invitationRepository->count(['status' => 'pending']),
            'accepted' => $this->invitationRepository->count(['status' => 'accepted']),
            'declined' => $this->invitationRepository->count(['status' => 'declined']),
            'expired' => $this->invitationRepository->count(['status' => 'expired']),
        ];
    }
}
