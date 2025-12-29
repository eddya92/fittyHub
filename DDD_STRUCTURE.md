# 🏗️ FITTY GYM - Struttura DDD Completa

## ✅ Lavoro Completato

### 1. Riorganizzazione Architetturale (DDD)

Il progetto è stato completamente riorganizzato secondo **Domain-Driven Design** con 7 domini bounded context:

```
src/Domain/
├── 📁 User/                 (Gestione Utenti)
│   ├── Entity/User.php
│   ├── Repository/UserRepository.php
│   └── UseCase/README.md    → 6 use case documentati
│
├── 📁 Gym/                  (Gestione Palestre)
│   ├── Entity/
│   │   ├── Gym.php
│   │   └── GymAttendance.php
│   ├── Repository/
│   │   ├── GymRepository.php
│   │   └── GymAttendanceRepository.php
│   └── UseCase/README.md    → 11 use case documentati
│
├── 📁 PersonalTrainer/      (PT e Relazioni Clienti)
│   ├── Entity/
│   │   ├── PersonalTrainer.php
│   │   └── PTClientRelation.php
│   ├── Repository/
│   │   ├── PersonalTrainerRepository.php
│   │   └── PTClientRelationRepository.php
│   └── UseCase/README.md    → 13 use case documentati
│
├── 📁 Membership/           (Abbonamenti)
│   ├── Entity/
│   │   ├── GymMembership.php
│   │   └── SubscriptionPlan.php
│   ├── Repository/
│   │   ├── GymMembershipRepository.php
│   │   └── SubscriptionPlanRepository.php
│   └── UseCase/README.md    → 16 use case documentati
│
├── 📁 Workout/              (Allenamenti)
│   ├── Entity/
│   │   ├── WorkoutPlan.php
│   │   ├── WorkoutExercise.php
│   │   └── WorkoutSession.php
│   ├── Repository/
│   │   ├── WorkoutPlanRepository.php
│   │   ├── WorkoutExerciseRepository.php
│   │   └── WorkoutSessionRepository.php
│   └── UseCase/README.md    → 20 use case documentati
│
├── 📁 Medical/              (Certificati Medici)
│   ├── Entity/MedicalCertificate.php
│   ├── Repository/MedicalCertificateRepository.php
│   └── UseCase/README.md    → 12 use case documentati
│
├── 📁 Invitation/           (Sistema Inviti)
│   ├── Entity/
│   │   ├── PTClientInvitation.php
│   │   └── GymPTInvitation.php
│   ├── Repository/
│   │   ├── PTClientInvitationRepository.php
│   │   └── GymPTInvitationRepository.php
│   └── UseCase/README.md    → 14 use case documentati
│
└── 📁 Shared/               (Codice Condiviso)
    ├── ValueObject/
    ├── Exception/
    └── Service/
```

### 2. Statistiche

- **13 Entity** create e spostate nei rispettivi domini
- **13 Repository** con query personalizzate
- **92 Use Case** totali documentati
- **7 Domini** bounded context
- **Namespace aggiornati** completamente
- **Doctrine configurato** per multi-domain mapping

## 📋 Use Case per Dominio

### User Domain (6 use case)
1. RegisterUser
2. LoginUser
3. UpdateUserProfile
4. UploadProfileImage
5. PromoteUserToPersonalTrainer
6. PromoteUserToGymAdmin

### Gym Domain (11 use case)
1. CreateGym
2. UpdateGymDetails
3. SetGymOpeningHours
4. SetGymAmenities
5. DeactivateGym
6. RecordCheckIn
7. RecordCheckOut
8. GetGymAttendanceStats
9. InviteInternalPT
10. AcceptGymPTInvitation
11. RemoveInternalPT

### PersonalTrainer Domain (13 use case)
1. CreatePersonalTrainerProfile
2. UpdatePersonalTrainerProfile
3. SetAvailabilityForNewClients
4. InviteClient
5. AcceptPTClientInvitation
6. RejectPTClientInvitation
7. SuspendClientRelation
8. TerminateClientRelation
9. AssignInternalPTToMember
10. GetPTClients
11. GetClientProgress
12. FindAvailablePTs
13. GetPTPublicProfile

### Membership Domain (16 use case)
1. CreateSubscriptionPlan
2. UpdateSubscriptionPlan
3. DeactivateSubscriptionPlan
4. GetActiveSubscriptionPlans
5. SubscribeToGym
6. ActivateMembership
7. SuspendMembership
8. CancelMembership
9. RenewMembership
10. ChangeSubscriptionPlan
11. ChooseInternalPT
12. ChangePT
13. ProcessPayment
14. GetExpiringMemberships
15. GetMembershipHistory
16. GetGymMembershipStats

### Workout Domain (20 use case)
1. CreateWorkoutPlan
2. UpdateWorkoutPlan
3. AddExerciseToPlan
4. UpdateExercise
5. RemoveExerciseFromPlan
6. ReorderExercises
7. SavePlanAsTemplate
8. CreatePlanFromTemplate
9. GetPTTemplates
10. LogWorkoutSession
11. CompleteWorkoutSession
12. GetClientWorkoutHistory
13. GetClientProgressStats
14. GetActivePlansForClient
15. GetPlanDetails
16. GetDayWorkout
17. DuplicatePlan
18. ArchivePlan
19. GetExerciseProgressChart
20. GetWorkoutFrequencyStats

### Medical Domain (12 use case)
1. UploadMedicalCertificate
2. UpdateMedicalCertificate
3. ReviewMedicalCertificate
4. GetPendingCertificatesForReview
5. CheckCertificateValidity
6. GetUserValidCertificate
7. GetExpiringCertificates
8. NotifyExpiringCertificate
9. SuspendMembershipForExpiredCertificate
10. GetUserCertificateHistory
11. DownloadCertificate
12. GetCertificateComplianceStats

### Invitation Domain (14 use case)
1. SendPTClientInvitation
2. ResendPTClientInvitation
3. AcceptPTClientInvitation
4. RejectPTClientInvitation
5. GetPTPendingInvitations
6. CancelPTClientInvitation
7. SendGymPTInvitation
8. AcceptGymPTInvitation
9. RejectGymPTInvitation
10. GetGymPendingInvitations
11. CancelGymPTInvitation
12. ExpireOldInvitations
13. ValidateInvitationToken
14. GetInvitationStats

## 🔧 Configurazione Symfony

### Doctrine Multi-Domain Mapping
```yaml
# config/packages/doctrine.yaml
mappings:
    User:
        dir: '%kernel.project_dir%/src/Domain/User/Entity'
        prefix: 'App\Domain\User\Entity'
    Gym:
        dir: '%kernel.project_dir%/src/Domain/Gym/Entity'
        prefix: 'App\Domain\Gym\Entity'
    # ... altri 5 domini
```

### Security Provider
```yaml
# config/packages/security.yaml
providers:
    app_user_provider:
        entity:
            class: App\Domain\User\Entity\User
            property: email
```

## 📖 Namespace Aggiornati

Tutti i namespace sono stati aggiornati:

**Prima:**
```php
namespace App\Entity;
use App\Repository\UserRepository;
```

**Dopo:**
```php
namespace App\Domain\User\Entity;
use App\Domain\User\Repository\UserRepository;
```

## 🎯 Pattern Use Case

La logica business andrà implementata nei **Use Case** invece che nei Controller:

```php
// ✅ CORRETTO - Use Case
namespace App\Domain\Membership\UseCase\SubscribeToGym;

class SubscribeToGymUseCase
{
    public function __construct(
        private GymMembershipRepository $membershipRepo,
        private SubscriptionPlanRepository $planRepo,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function execute(SubscribeToGymCommand $command): GymMembership
    {
        // 1. Validazioni business
        $this->validateBusinessRules($command);

        // 2. Creazione oggetto dominio
        $membership = $this->createMembership($command);

        // 3. Persistenza
        $this->membershipRepo->save($membership);

        // 4. Eventi
        $this->eventDispatcher->dispatch(
            new MembershipCreatedEvent($membership)
        );

        return $membership;
    }

    private function validateBusinessRules(SubscribeToGymCommand $command): void
    {
        // Business rules validation
    }

    private function createMembership(SubscribeToGymCommand $command): GymMembership
    {
        // Factory logic
    }
}
```

```php
// Controller THIN
namespace App\Application\Controller;

class MembershipController extends AbstractController
{
    #[Route('/membership/subscribe', methods: ['POST'])]
    public function subscribe(
        Request $request,
        SubscribeToGymUseCase $useCase
    ): JsonResponse {
        $command = new SubscribeToGymCommand(
            userId: $this->getUser()->getId(),
            gymId: $request->request->getInt('gymId'),
            subscriptionPlanId: $request->request->getInt('planId'),
            startDate: new \DateTime(),
        );

        $membership = $useCase->execute($command);

        return $this->json(['id' => $membership->getId()]);
    }
}
```

## 📁 Cartelle Applicative

Oltre ai Domini, abbiamo preparato:

```
src/Application/
├── Controller/     → Controller HTTP (thin layer)
├── Form/          → Symfony Forms
└── Security/      → Voters, Guards, Authenticators
```

## 🚀 Prossimi Step

### Fase 1: Database Setup
```bash
# Avvia MySQL
# Poi:
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Fase 2: Implementare Use Case Prioritari
Iniziare con i use case critici in questo ordine:

1. **User Domain**
   - RegisterUser
   - LoginUser

2. **Gym Domain**
   - CreateGym
   - RecordCheckIn/CheckOut

3. **Membership Domain**
   - CreateSubscriptionPlan
   - SubscribeToGym
   - ActivateMembership

4. **Medical Domain**
   - UploadMedicalCertificate
   - ReviewMedicalCertificate

5. **PersonalTrainer Domain**
   - CreatePersonalTrainerProfile
   - InviteClient

6. **Workout Domain**
   - CreateWorkoutPlan
   - LogWorkoutSession

7. **Invitation Domain**
   - SendPTClientInvitation
   - AcceptPTClientInvitation

### Fase 3: Controller e UI
- Creare Controller che utilizzano i Use Case
- Creare Form per validazione input
- Creare Template Twig per interfaccia

## 💡 Vantaggi Architettura Attuale

✅ **Testabilità**: Use Case testabili in isolamento
✅ **Manutenibilità**: Business logic separata da framework
✅ **Scalabilità**: Facile aggiungere nuovi domini
✅ **Team Work**: Team diversi su domini diversi
✅ **Riusabilità**: Use Case riutilizzabili (API, CLI, Jobs)
✅ **Chiarezza**: Responsabilità ben definite

## 📚 Documentazione

Ogni dominio ha il suo README dettagliato:
- `src/Domain/README.md` - Panoramica architettura
- `src/Domain/User/UseCase/README.md` - Use case User
- `src/Domain/Gym/UseCase/README.md` - Use case Gym
- `src/Domain/PersonalTrainer/UseCase/README.md` - Use case PT
- `src/Domain/Membership/UseCase/README.md` - Use case Membership
- `src/Domain/Workout/UseCase/README.md` - Use case Workout
- `src/Domain/Medical/UseCase/README.md` - Use case Medical
- `src/Domain/Invitation/UseCase/README.md` - Use case Invitation

---

**La struttura è pronta per iniziare l'implementazione dei Use Case! 🎉**
