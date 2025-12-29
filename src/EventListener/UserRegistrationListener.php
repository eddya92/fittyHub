<?php

namespace App\EventListener;

use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500)]
class UserRegistrationListener
{
    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        // Invia email solo per nuovi User
        if (!$entity instanceof User) {
            return;
        }

        $this->sendWelcomeEmail($entity);
    }

    private function sendWelcomeEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from('noreply@fittyhub.com')
            ->to($user->getEmail())
            ->subject('Benvenuto su FITTY HUB! 🎉')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'user' => $user
            ]);

        $this->mailer->send($email);
    }
}
