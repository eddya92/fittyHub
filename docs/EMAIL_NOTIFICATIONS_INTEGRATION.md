# Integrazione Email Notifications

Questa guida mostra dove e come integrare le chiamate alle notifiche email nel codice esistente.

## ✅ Notifiche Implementate

### 1. Conferma/Cancellazione Prenotazione Corso

**Dove**: `src/Domain/Membership/Service/EnrollmentService.php`

**Quando chiamare**:
- ✅ `sendCourseEnrollmentConfirmation()` → dopo `enrollUserToSession()` con successo
- ✅ `sendCourseEnrollmentCancellation()` → dopo cancellazione iscrizione

**Esempio integrazione**:
```php
// In EnrollmentService::enrollUserToSession()
public function enrollUserToSession(CourseSession $session, User $user): CourseEnrollment
{
    // ... validazioni e creazione enrollment ...

    $this->enrollmentRepository->save($enrollment, true);

    // ✉️ Invia email conferma prenotazione
    $this->emailService->sendCourseEnrollmentConfirmation($enrollment);

    return $enrollment;
}

// Nuovo metodo da creare: cancelEnrollment()
public function cancelEnrollment(CourseEnrollment $enrollment): void
{
    if ($enrollment->getStatus() !== 'active') {
        throw new \RuntimeException('Questa prenotazione non può essere cancellata');
    }

    $enrollment->setStatus('cancelled');
    $enrollment->setCancelledAt(new \DateTime());

    $this->enrollmentRepository->save($enrollment, true);

    // ✉️ Invia email cancellazione prenotazione
    $this->emailService->sendCourseEnrollmentCancellation($enrollment);
}
```

---

### 2. Promemoria Corso (1 ora prima)

**Dove**: Command schedulato da CRON

**File**: `src/Command/SendCourseRemindersCommand.php` ✅ Già implementato

**Configurazione CRON**: Vedi `docs/CRON_SETUP.md`

**Nessuna integrazione manuale richiesta** - funziona automaticamente.

---

### 3. Scadenza Certificato Medico

**Dove**: Command schedulato da CRON

**File**: `src/Command/SendCertificateExpiryRemindersCommand.php` ✅ Già implementato

**Configurazione CRON**: Vedi `docs/CRON_SETUP.md`

**Nessuna integrazione manuale richiesta** - funziona automaticamente.

---

### 4. Nuovo Piano Allenamento Assegnato

**Dove**: `src/Domain/Workout/UseCase/CreateWorkoutPlanUseCase.php`

**Quando chiamare**: Dopo creazione piano di tipo `trainer_created`

**Esempio integrazione**:
```php
// In CreateWorkoutPlanUseCase::execute()
public function execute(...): WorkoutPlan
{
    // ... validazioni e creazione piano ...

    $this->entityManager->persist($workoutPlan);
    $this->entityManager->flush();

    // ✉️ Invia email solo se creato da PT
    if ($planType === 'trainer_created') {
        $this->emailService->sendNewWorkoutPlanAssigned($workoutPlan);
    }

    return $workoutPlan;
}
```

**Richiede**: Iniettare `EmailService` nel costruttore del UseCase.

---

### 5. Invito PT Accettato/Rifiutato

**Dove**: `src/Domain/Invitation/Service/PTClientInvitationService.php` (o controller che gestisce risposta)

**Quando chiamare**:
- ✅ `sendPTInvitationAccepted()` → quando cliente accetta invito
- ✅ `sendPTInvitationRejected()` → quando cliente rifiuta invito

**Esempio integrazione**:
```php
// Nuovo metodo da creare nel service o controller
public function acceptInvitation(PTClientInvitation $invitation, User $client): void
{
    if ($invitation->getClient() !== $client) {
        throw new \RuntimeException('Non autorizzato');
    }

    if ($invitation->getStatus() !== 'pending') {
        throw new \RuntimeException('Invito già processato');
    }

    $invitation->setStatus('accepted');
    $invitation->setRespondedAt(new \DateTime());

    // Crea relazione PT-Cliente
    $relation = new PTClientRelation();
    $relation->setPersonalTrainer($invitation->getPersonalTrainer());
    $relation->setClient($client);
    $relation->setStatus('active');

    $this->entityManager->persist($relation);
    $this->entityManager->persist($invitation);
    $this->entityManager->flush();

    // ✉️ Notifica al PT
    $this->emailService->sendPTInvitationAccepted($invitation);
}

public function rejectInvitation(PTClientInvitation $invitation, User $client, ?string $reason = null): void
{
    // ... validazioni simili ...

    $invitation->setStatus('rejected');
    $invitation->setRespondedAt(new \DateTime());
    $invitation->setRejectionReason($reason);

    $this->entityManager->persist($invitation);
    $this->entityManager->flush();

    // ✉️ Notifica al PT
    $this->emailService->sendPTInvitationRejected($invitation);
}
```

---

### 6. Check-in Effettuato

**Dove**: `src/Domain/Gym/Service/CheckInService.php`

**Quando chiamare**: Dopo check-in con successo

**Esempio integrazione**:
```php
// In CheckInService::checkIn()
public function checkIn(User $user, Gym $gym, string $type = 'gym_entrance'): GymAttendance
{
    // ... validazioni ...

    $attendance = new GymAttendance();
    // ... setup attendance ...

    $this->attendanceRepository->save($attendance, true);

    // ✉️ Invia email conferma check-in (opzionale, potrebbe essere troppo frequente)
    // $this->emailService->sendCheckInConfirmation($attendance);

    return $attendance;
}
```

**Nota**: Questa notifica è **opzionale** - potrebbe essere eccessiva se l'utente fa check-in ogni giorno. Considera di:
- Renderla configurabile (impostazione utente)
- Inviarla solo per eventi speciali (corsi, sessioni PT)
- Inviarla come riepilogo settimanale invece che ad ogni check-in

---

### 7. Raggiungimento Obiettivo

**Dove**: Da implementare in logica business specifica

**Esempi di quando chiamare**:
- Completamento di un piano allenamento
- Raggiungimento X presenze in palestra
- Completamento X sessioni con PT
- Milestone personalizzati

**Esempio integrazione**:
```php
// Esempio: dopo completamento workout plan
public function completeWorkoutPlan(WorkoutPlan $plan): void
{
    $plan->setIsActive(false);
    $plan->setCompletedAt(new \DateTime());

    $this->entityManager->persist($plan);
    $this->entityManager->flush();

    // ✉️ Notifica raggiungimento obiettivo
    $this->emailService->sendGoalAchieved(
        $plan->getClient(),
        'workout_plan_completed',
        sprintf('Piano "%s" completato in %d settimane', $plan->getName(), $plan->getWeeksCount())
    );
}

// Esempio: milestone presenze
public function checkAttendanceMilestone(User $user, Gym $gym): void
{
    $totalAttendances = $this->attendanceRepository->countByUserAndGym($user, $gym);

    // Milestone ogni 10, 25, 50, 100 presenze
    if (in_array($totalAttendances, [10, 25, 50, 100])) {
        $this->emailService->sendGoalAchieved(
            $user,
            'attendance_milestone',
            sprintf('%d presenze in palestra raggiunte!', $totalAttendances)
        );
    }
}
```

---

## Checklist Integrazione

### Da fare subito:
- [ ] Iniettare `EmailService` in `EnrollmentService`
- [ ] Aggiungere `sendCourseEnrollmentConfirmation()` dopo iscrizione corso
- [ ] Creare metodo `cancelEnrollment()` in `EnrollmentService`
- [ ] Iniettare `EmailService` in `CreateWorkoutPlanUseCase`
- [ ] Aggiungere `sendNewWorkoutPlanAssigned()` dopo creazione piano PT
- [ ] Creare metodi `acceptInvitation()` e `rejectInvitation()` per inviti PT

### Opzionale:
- [ ] Decidere se abilitare notifica check-in
- [ ] Implementare logica milestone/obiettivi
- [ ] Configurare CRON per promemoria e scadenze (vedi `docs/CRON_SETUP.md`)

### Test:
- [ ] Testare invio email in ambiente dev
- [ ] Configurare MAILER_DSN in `.env`
- [ ] Verificare template email su client email reale
- [ ] Testare command CRON manualmente prima di schedulare

---

## Configurazione Mailer

Nel file `.env`:
```env
# Gmail (esempio)
MAILER_DSN=gmail://username:app-password@default

# SMTP generico
MAILER_DSN=smtp://user:pass@smtp.example.com:465

# Mailtrap (dev/testing)
MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525
```

Test invio:
```bash
php bin/console mailer:test your-email@example.com
```

---

## Prossimi Step

1. **Implementare le integrazioni** seguendo gli esempi sopra
2. **Configurare CRON** per notifiche automatiche
3. **Testare** in ambiente di sviluppo
4. **Monitorare** i log di invio email
5. **Opzionale**: Aggiungere preferenze utente per controllo notifiche
