<?php

namespace App\Command;

use App\Domain\Gym\Repository\GymRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:gym:generate-slugs',
    description: 'Genera slug per le palestre che non ce l\'hanno',
)]
class GenerateGymSlugsCommand extends Command
{
    public function __construct(
        private GymRepositoryInterface $gymRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generazione Slug Palestre');

        // Trova tutte le palestre
        $gyms = $this->gymRepository->findAll();

        if (empty($gyms)) {
            $io->warning('Nessuna palestra trovata nel database.');
            return Command::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($gyms as $gym) {
            $slug = $gym->getSlug();
            if ($slug === null || $slug === '' || trim($slug) === '') {
                $gym->generateSlug();
                $this->entityManager->persist($gym);
                $updated++;
                $io->success(sprintf(
                    'Slug generato per "%s": %s',
                    $gym->getName(),
                    $gym->getSlug()
                ));
            } else {
                $skipped++;
                $io->info(sprintf(
                    'Palestra "%s" ha già uno slug: %s',
                    $gym->getName(),
                    $gym->getSlug()
                ));
            }
        }

        if ($updated > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d slug generati con successo!', $updated));
        }

        if ($skipped > 0) {
            $io->note(sprintf('%d palestre già avevano uno slug.', $skipped));
        }

        return Command::SUCCESS;
    }
}
