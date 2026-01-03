<?php

namespace App\Command;

use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-course-sessions',
    description: 'Lista tutte le sessioni dei corsi',
)]
class ListCourseSessionsCommand extends Command
{
    public function __construct(
        private CourseSessionRepositoryInterface $sessionRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $startDate = new \DateTime('monday this week');
        $endDate = (clone $startDate)->modify('+8 weeks');

        $sessions = $this->sessionRepository->findBetweenDates($startDate, $endDate);

        $io->title('Sessioni corsi');
        $io->info("Settimana corrente: inizio = " . $startDate->format('Y-m-d (l)'));

        $rows = [];
        foreach ($sessions as $session) {
            $rows[] = [
                $session->getId(),
                $session->getCourse()->getName(),
                $session->getSessionDate()->format('Y-m-d (l)'),
                $session->getSchedule()->getStartTime()->format('H:i'),
                $session->getSchedule()->getEndTime()->format('H:i'),
                $session->getStatus(),
                $session->getActiveEnrollmentsCount() . '/' . $session->getMaxParticipants(),
            ];
        }

        $io->table(
            ['ID', 'Corso', 'Data', 'Inizio', 'Fine', 'Status', 'Iscritti'],
            $rows
        );

        $io->info("Totale sessioni: " . count($sessions));

        return Command::SUCCESS;
    }
}
