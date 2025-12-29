<?php

namespace App\Command;

use App\Domain\Invitation\Repository\GymPTInvitationRepository;
use App\Domain\Invitation\Repository\PTClientInvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:invitations:expire',
    description: 'Marca come scaduti gli inviti oltre la data di scadenza'
)]
class ExpireInvitationsCommand extends Command
{
    public function __construct(
        private PTClientInvitationRepository $ptClientInvitationRepo,
        private GymPTInvitationRepository $gymPTInvitationRepo,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $now = new \DateTimeImmutable();
        $expiredCount = 0;

        // Trova inviti PT-Cliente scaduti
        $ptInvitations = $this->ptClientInvitationRepo->findBy(['status' => 'pending']);
        foreach ($ptInvitations as $invitation) {
            if ($invitation->getExpiresAt() < $now) {
                $invitation->setStatus('expired');
                $expiredCount++;
            }
        }

        // Trova inviti Palestra-PT scaduti
        $gymInvitations = $this->gymPTInvitationRepo->findBy(['status' => 'pending']);
        foreach ($gymInvitations as $invitation) {
            if ($invitation->getExpiresAt() < $now) {
                $invitation->setStatus('expired');
                $expiredCount++;
            }
        }

        $this->entityManager->flush();

        $io->success("$expiredCount inviti marcati come scaduti.");

        return Command::SUCCESS;
    }
}
