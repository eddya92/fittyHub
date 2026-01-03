<?php

namespace App\Command;

use App\Domain\Course\UseCase\GenerateCourseSessionsUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-course-sessions',
    description: 'Genera sessioni specifiche per i corsi attivi',
)]
class GenerateCourseSessionsCommand extends Command
{
    public function __construct(
        private GenerateCourseSessionsUseCase $generateSessions
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('weeks', 'w', InputOption::VALUE_OPTIONAL, 'Numero di settimane da generare', 8);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $weeks = (int)$input->getOption('weeks');

        $io->title('Generazione sessioni corsi');
        $io->info("Genero sessioni per le prossime {$weeks} settimane...");

        $sessionsCreated = $this->generateSessions->execute($weeks, true);

        if ($sessionsCreated > 0) {
            $io->success("Create {$sessionsCreated} nuove sessioni!");
        } else {
            $io->warning('Nessuna nuova sessione creata (potrebbero già esistere)');
        }

        return Command::SUCCESS;
    }
}
