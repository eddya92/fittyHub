<?php

namespace App\Command;

use App\Domain\Course\UseCase\RegenerateFutureSessionsUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:regenerate-course-sessions',
    description: 'Pulisce e rigenera tutte le sessioni future dei corsi',
)]
class RegenerateCourseSessionsCommand extends Command
{
    public function __construct(
        private RegenerateFutureSessionsUseCase $regenerateSessions
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('weeks', 'w', InputOption::VALUE_OPTIONAL, 'Numero di settimane da generare', 5)
            ->addOption('monthly', 'm', InputOption::VALUE_NONE, 'Rigenera per mese corrente + prossimo (circa 8 settimane)')
            ->setHelp(
                <<<'HELP'
Questo comando elimina tutte le sessioni future programmate e le ricrea
basandosi sull'ultima configurazione degli orari.

Esempi:
  # Rigenera per le prossime 5 settimane (default)
  php bin/console app:regenerate-course-sessions

  # Rigenera per le prossime 8 settimane
  php bin/console app:regenerate-course-sessions --weeks=8

  # Rigenera per mese corrente + prossimo (per cron mensile)
  php bin/console app:regenerate-course-sessions --monthly

Configurazione cron (ogni 1° del mese alle 00:00):
0 0 1 * * php /path/to/bin/console app:regenerate-course-sessions --monthly
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Rigenerazione sessioni corsi');

        if ($input->getOption('monthly')) {
            $io->info('Modalità mensile: rigenero sessioni per mese corrente + prossimo...');
            $result = $this->regenerateSessions->regenerateCurrentAndNextMonth();
        } else {
            $weeks = (int)$input->getOption('weeks');
            $io->info("Rigenero sessioni per le prossime {$weeks} settimane...");
            $result = $this->regenerateSessions->execute($weeks);
        }

        $io->section('Risultato');
        $io->listing([
            "Sessioni eliminate: {$result['deleted']}",
            "Sessioni create: {$result['created']}",
        ]);

        if ($result['created'] > 0) {
            $io->success('Sessioni rigenerate con successo!');
        } else {
            $io->warning('Nessuna nuova sessione creata. Verifica che ci siano corsi attivi con orari configurati.');
        }

        return Command::SUCCESS;
    }
}
