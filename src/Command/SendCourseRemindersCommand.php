<?php

namespace App\Command;

use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use App\Infrastructure\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:courses:send-reminders',
    description: 'Invia promemoria email per corsi che iniziano tra 1 ora'
)]
class SendCourseRemindersCommand extends Command
{
    public function __construct(
        private CourseSessionRepositoryInterface $sessionRepository,
        private EmailService $emailService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Trova sessioni che iniziano tra 55 e 65 minuti (finestra di 10 minuti)
        $now = new \DateTime();
        $oneHourLater = (clone $now)->modify('+1 hour');
        $startWindow = (clone $oneHourLater)->modify('-5 minutes');
        $endWindow = (clone $oneHourLater)->modify('+5 minutes');

        $sessions = $this->sessionRepository->findUpcomingSessions($startWindow, $endWindow);

        $remindersSent = 0;

        foreach ($sessions as $session) {
            if ($session->getStatus() !== 'scheduled') {
                continue;
            }

            // Invia promemoria a tutti gli iscritti
            foreach ($session->getEnrollments() as $enrollment) {
                if ($enrollment->getStatus() === 'active') {
                    try {
                        $this->emailService->sendCourseReminder($enrollment);
                        $remindersSent++;
                    } catch (\Exception $e) {
                        $io->error(sprintf(
                            'Errore invio promemoria a %s: %s',
                            $enrollment->getUser()->getEmail(),
                            $e->getMessage()
                        ));
                    }
                }
            }
        }

        $io->success(sprintf('Inviati %d promemoria per %d sessioni.', $remindersSent, count($sessions)));

        return Command::SUCCESS;
    }
}
