<?php

namespace App\Command;

use App\Domain\Membership\Repository\GymMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:memberships:expire',
    description: 'Marca come scaduti gli abbonamenti oltre la data di fine'
)]
class ExpireMembershipsCommand extends Command
{
    public function __construct(
        private GymMembershipRepository $membershipRepo,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $today = new \DateTimeImmutable();
        $expiredCount = 0;

        // Trova abbonamenti attivi scaduti
        $memberships = $this->membershipRepo->findBy(['status' => 'active']);
        foreach ($memberships as $membership) {
            if ($membership->getEndDate() < $today) {
                $membership->setStatus('expired');
                $expiredCount++;
            }
        }

        $this->entityManager->flush();

        $io->success("$expiredCount abbonamenti marcati come scaduti.");

        return Command::SUCCESS;
    }
}
