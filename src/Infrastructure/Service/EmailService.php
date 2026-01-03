<?php

namespace App\Infrastructure\Service;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Entity\CourseSession;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Invitation\Entity\PTClientInvitation;
use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Entity\MembershipRequest;
use App\Domain\User\Entity\User;
use App\Domain\Workout\Entity\WorkoutPlan;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Servizio centralizzato per invio email
 */
class EmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    /**
     * Email all'utente: conferma richiesta iscrizione inviata
     */
    public function sendMembershipRequestConfirmation(MembershipRequest $request): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($request->getUser()->getEmail(), $request->getUser()->getFirstName() . ' ' . $request->getUser()->getLastName()))
            ->subject('Richiesta di iscrizione ricevuta - ' . $request->getGym()->getName())
            ->htmlTemplate('emails/membership_request_confirmation.html.twig')
            ->context([
                'request' => $request,
                'user' => $request->getUser(),
                'gym' => $request->getGym(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email agli admin palestra: nuova richiesta iscrizione da gestire
     */
    public function sendNewMembershipRequestToAdmins(MembershipRequest $request): void
    {
        $gym = $request->getGym();

        // Invia email a tutti gli admin della palestra
        foreach ($gym->getAdmins() as $admin) {
            $email = (new TemplatedEmail())
                ->to(new Address($admin->getEmail(), $admin->getFirstName() . ' ' . $admin->getLastName()))
                ->subject('[FittyHub] Nuova richiesta iscrizione - ' . $gym->getName())
                ->htmlTemplate('emails/new_membership_request_admin.html.twig')
                ->context([
                    'request' => $request,
                    'user' => $request->getUser(),
                    'gym' => $gym,
                    'admin' => $admin,
                ]);

            $this->mailer->send($email);
        }
    }

    /**
     * Email all'utente: richiesta iscrizione approvata
     */
    public function sendMembershipRequestApproved(MembershipRequest $request, GymMembership $membership): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($request->getUser()->getEmail(), $request->getUser()->getFirstName() . ' ' . $request->getUser()->getLastName()))
            ->subject('🎉 Iscrizione approvata - ' . $request->getGym()->getName())
            ->htmlTemplate('emails/membership_request_approved.html.twig')
            ->context([
                'request' => $request,
                'membership' => $membership,
                'user' => $request->getUser(),
                'gym' => $request->getGym(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: richiesta iscrizione rifiutata
     */
    public function sendMembershipRequestRejected(MembershipRequest $request): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($request->getUser()->getEmail(), $request->getUser()->getFirstName() . ' ' . $request->getUser()->getLastName()))
            ->subject('Richiesta iscrizione - ' . $request->getGym()->getName())
            ->htmlTemplate('emails/membership_request_rejected.html.twig')
            ->context([
                'request' => $request,
                'user' => $request->getUser(),
                'gym' => $request->getGym(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: abbonamento in scadenza
     */
    public function sendMembershipExpiring(GymMembership $membership, int $daysRemaining): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($membership->getUser()->getEmail(), $membership->getUser()->getFirstName() . ' ' . $membership->getUser()->getLastName()))
            ->subject('⏰ Il tuo abbonamento sta per scadere - ' . $membership->getGym()->getName())
            ->htmlTemplate('emails/membership_expiring.html.twig')
            ->context([
                'membership' => $membership,
                'user' => $membership->getUser(),
                'gym' => $membership->getGym(),
                'days_remaining' => $daysRemaining,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: abbonamento scaduto
     */
    public function sendMembershipExpired(GymMembership $membership): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($membership->getUser()->getEmail(), $membership->getUser()->getFirstName() . ' ' . $membership->getUser()->getLastName()))
            ->subject('Abbonamento scaduto - ' . $membership->getGym()->getName())
            ->htmlTemplate('emails/membership_expired.html.twig')
            ->context([
                'membership' => $membership,
                'user' => $membership->getUser(),
                'gym' => $membership->getGym(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: conferma prenotazione corso
     */
    public function sendCourseEnrollmentConfirmation(CourseEnrollment $enrollment): void
    {
        $session = $enrollment->getSession();
        $user = $enrollment->getUser();

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('✅ Prenotazione confermata - ' . $session->getCourse()->getName())
            ->htmlTemplate('emails/course_enrollment_confirmation.html.twig')
            ->context([
                'enrollment' => $enrollment,
                'session' => $session,
                'course' => $session->getCourse(),
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: cancellazione prenotazione corso
     */
    public function sendCourseEnrollmentCancellation(CourseEnrollment $enrollment): void
    {
        $session = $enrollment->getSession();
        $user = $enrollment->getUser();

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('Prenotazione cancellata - ' . $session->getCourse()->getName())
            ->htmlTemplate('emails/course_enrollment_cancellation.html.twig')
            ->context([
                'enrollment' => $enrollment,
                'session' => $session,
                'course' => $session->getCourse(),
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: promemoria corso (1 ora prima)
     */
    public function sendCourseReminder(CourseEnrollment $enrollment): void
    {
        $session = $enrollment->getSession();
        $user = $enrollment->getUser();

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('⏰ Promemoria: ' . $session->getCourse()->getName() . ' tra 1 ora')
            ->htmlTemplate('emails/course_reminder.html.twig')
            ->context([
                'enrollment' => $enrollment,
                'session' => $session,
                'course' => $session->getCourse(),
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: certificato medico in scadenza
     */
    public function sendMedicalCertificateExpiring(MedicalCertificate $certificate, int $daysRemaining): void
    {
        $user = $certificate->getUser();

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('⚠️ Certificato medico in scadenza tra ' . $daysRemaining . ' giorni')
            ->htmlTemplate('emails/medical_certificate_expiring.html.twig')
            ->context([
                'certificate' => $certificate,
                'user' => $user,
                'gym' => $certificate->getGym(),
                'days_remaining' => $daysRemaining,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: nuovo piano allenamento assegnato
     */
    public function sendNewWorkoutPlanAssigned(WorkoutPlan $plan): void
    {
        $user = $plan->getClient();
        $trainer = $plan->getPersonalTrainer();

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('🏋️ Nuovo piano allenamento assegnato')
            ->htmlTemplate('emails/workout_plan_assigned.html.twig')
            ->context([
                'plan' => $plan,
                'user' => $user,
                'trainer' => $trainer,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email al PT: invito accettato dal cliente
     */
    public function sendPTInvitationAccepted(PTClientInvitation $invitation): void
    {
        $trainer = $invitation->getPersonalTrainer();
        $client = $invitation->getClient();

        $email = (new TemplatedEmail())
            ->to(new Address($trainer->getUser()->getEmail(), $trainer->getUser()->getFirstName() . ' ' . $trainer->getUser()->getLastName()))
            ->subject('✅ Invito accettato da ' . $client->getFirstName() . ' ' . $client->getLastName())
            ->htmlTemplate('emails/pt_invitation_accepted.html.twig')
            ->context([
                'invitation' => $invitation,
                'trainer' => $trainer,
                'client' => $client,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email al PT: invito rifiutato dal cliente
     */
    public function sendPTInvitationRejected(PTClientInvitation $invitation): void
    {
        $trainer = $invitation->getPersonalTrainer();
        $client = $invitation->getClient();

        $email = (new TemplatedEmail())
            ->to(new Address($trainer->getUser()->getEmail(), $trainer->getUser()->getFirstName() . ' ' . $trainer->getUser()->getLastName()))
            ->subject('❌ Invito rifiutato da ' . $client->getFirstName() . ' ' . $client->getLastName())
            ->htmlTemplate('emails/pt_invitation_rejected.html.twig')
            ->context([
                'invitation' => $invitation,
                'trainer' => $trainer,
                'client' => $client,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: conferma check-in
     */
    public function sendCheckInConfirmation(GymAttendance $attendance): void
    {
        $user = $attendance->getUser();
        $gym = $attendance->getGym();

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('✅ Check-in effettuato - ' . $gym->getName())
            ->htmlTemplate('emails/checkin_confirmation.html.twig')
            ->context([
                'attendance' => $attendance,
                'user' => $user,
                'gym' => $gym,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Email all'utente: raggiungimento obiettivo
     */
    public function sendGoalAchieved(User $user, string $goalType, string $goalDescription): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('🎉 Obiettivo raggiunto!')
            ->htmlTemplate('emails/goal_achieved.html.twig')
            ->context([
                'user' => $user,
                'goal_type' => $goalType,
                'goal_description' => $goalDescription,
            ]);

        $this->mailer->send($email);
    }
}
