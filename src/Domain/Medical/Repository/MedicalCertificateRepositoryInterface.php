<?php

namespace App\Domain\Medical\Repository;

use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\User\Entity\User;

/**
 * Repository interface per MedicalCertificate
 *
 * Nota: Metodi standard (find, findBy, save, remove, count, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface MedicalCertificateRepositoryInterface
{
    /**
     * Trova certificati con filtri custom
     */
    public function findWithFilters(?string $status, ?string $search): array;

    /**
     * Trova certificato medico valido per un utente
     * (approvato e non scaduto)
     */
    public function findValidCertificateForUser(User $user): ?MedicalCertificate;

    /**
     * Trova certificato medico valido per un utente in una specifica palestra
     * (approvato, non scaduto, per gym specifica)
     */
    public function findValidCertificateForUserAndGym(User $user, $gym): ?MedicalCertificate;

    /**
     * Trova certificati che scadono in una data specifica
     */
    public function findExpiringOn(\DateTimeInterface $date): array;
}
