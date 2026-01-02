<?php

namespace App\Domain\Invitation\Service;

use App\Domain\Invitation\Entity\PTClientInvitation;
use App\Domain\Invitation\Repository\PTClientInvitationRepositoryInterface;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\PersonalTrainer\Repository\PTClientRelationRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PTClientInvitationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PTClientInvitationRepositoryInterface $invitationRepository,
        private UserRepositoryInterface $userRepository,
        private PTClientRelationRepositoryInterface $relationRepository,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function createInvitation(
        PersonalTrainer $trainer,
        string $clientEmail,
        ?string $message = null
    ): PTClientInvitation {
        // Controlla se esiste già un invito pendente
        $existingInvitation = $this->invitationRepository->findOneBy([
            'personalTrainer' => $trainer,
            'clientEmail' => $clientEmail,
            'status' => 'pending'
        ]);

        if ($existingInvitation && $existingInvitation->isPending()) {
            throw new \DomainException('Esiste già un invito pendente per questo cliente');
        }

        // Controlla se il cliente è già collegato
        $existingUser = $this->userRepository->findOneBy(['email' => $clientEmail]);
        if ($existingUser) {
            $existingRelation = $this->relationRepository->findOneBy([
                'personalTrainer' => $trainer,
                'client' => $existingUser,
                'status' => 'active'
            ]);

            if ($existingRelation) {
                throw new \DomainException('Questo utente è già tuo cliente');
            }
        }

        $invitation = new PTClientInvitation();
        $invitation->setPersonalTrainer($trainer);
        $invitation->setClientEmail($clientEmail);
        $invitation->setMessage($message);

        if ($existingUser) {
            $invitation->setClientUser($existingUser);
        }

        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        $this->sendInvitationEmail($invitation);

        return $invitation;
    }

    public function acceptInvitation(string $token): PTClientRelation
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

        $client = $invitation->getClientUser();
        if (!$client) {
            throw new \DomainException('Devi prima registrarti per accettare questo invito');
        }

        // Crea la relazione PT-Cliente
        $relation = new PTClientRelation();
        $relation->setPersonalTrainer($invitation->getPersonalTrainer());
        $relation->setClient($client);
        $relation->setStatus('active');
        $relation->setStartDate(new \DateTimeImmutable());

        $this->entityManager->persist($relation);

        // Aggiorna stato invito
        $invitation->setStatus('accepted');
        $invitation->setRespondedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $relation;
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

    private function sendInvitationEmail(PTClientInvitation $invitation): void
    {
        $trainer = $invitation->getPersonalTrainer();
        $trainerUser = $trainer->getUser();

        $acceptUrl = $this->urlGenerator->generate(
            'app_invitation_pt_client_accept',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $declineUrl = $this->urlGenerator->generate(
            'app_invitation_pt_client_decline',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from('noreply@fittyhub.com')
            ->to($invitation->getClientEmail())
            ->subject('Invito Personal Training da ' . $trainerUser->getFirstName() . ' ' . $trainerUser->getLastName())
            ->htmlTemplate('emails/pt_client_invitation.html.twig')
            ->context([
                'clientName' => $invitation->getClientUser()
                    ? $invitation->getClientUser()->getFirstName()
                    : 'Cliente',
                'trainerName' => $trainerUser->getFirstName() . ' ' . $trainerUser->getLastName(),
                'message' => $invitation->getMessage(),
                'acceptUrl' => $acceptUrl,
                'declineUrl' => $declineUrl,
                'expiresAt' => $invitation->getExpiresAt()
            ]);

        $this->mailer->send($email);
    }
}
