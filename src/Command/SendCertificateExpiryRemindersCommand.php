<?php

namespace App\Command;

use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Infrastructure\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:certificates:send-expiry-reminders',
    description: 'Invia promemoria per certificati medici in scadenza (30, 14, 7 giorni prima)'
)]
class SendCertificateExpiryRemindersCommand extends Command
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository,
        private EmailService $emailService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $today = new \DateTimeImmutable();
        $remindersDays = [30, 14, 7]; // Giorni prima della scadenza
        $totalSent = 0;

        foreach ($remindersDays as $days) {
            $expiryDate = $today->modify("+{$days} days");

            // Trova certificati che scadono esattamente tra N giorni
            $certificates = $this->certificateRepository->findExpiringOn($expiryDate);

            foreach ($certificates as $certificate) {
                // Verifica che il certificato sia ancora approvato
                if ($certificate->getStatus() !== 'approved') {
                    continue;
                }

                try {
                    $this->emailService->sendMedicalCertificateExpiring($certificate, $days);

                    $totalSent++;

                    $io->writeln(sprintf(
                        'Promemoria inviato a %s (%d giorni)',
                        $certificate->getUser()->getEmail(),
                        $days
                    ));
                } catch (\Exception $e) {
                    $io->error(sprintf(
                        'Errore invio promemoria a %s: %s',
                        $certificate->getUser()->getEmail(),
                        $e->getMessage()
                    ));
                }
            }
        }

        $io->success(sprintf('Inviati %d promemoria per scadenza certificati.', $totalSent));

        return Command::SUCCESS;
    }
}
