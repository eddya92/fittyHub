# FITTY GYM - Architettura DDD

## 📐 Struttura Domini

Il progetto è organizzato secondo i principi **Domain-Driven Design (DDD)** con separazione chiara tra domini e livelli applicativi.

```
src/
├── Domain/                    # Layer di Dominio (Business Logic)
│   ├── User/
│   │   ├── Entity/           # User
│   │   ├── Repository/       # UserRepository
│   │   └── UseCase/          # RegisterUser, LoginUser, UpdateProfile...
│   │
│   ├── Gym/
│   │   ├── Entity/           # Gym, GymAttendance
│   │   ├── Repository/       # GymRepository, GymAttendanceRepository
│   │   └── UseCase/          # CreateGym, RecordCheckIn, GetAttendanceStats...
│   │
│   ├── PersonalTrainer/
│   │   ├── Entity/           # PersonalTrainer, PTClientRelation
│   │   ├── Repository/       # PersonalTrainerRepository, PTClientRelationRepository
│   │   └── UseCase/          # CreatePTProfile, InviteClient, AssignPTToMember...
│   │
│   ├── Membership/
│   │   ├── Entity/           # GymMembership, SubscriptionPlan
│   │   ├── Repository/       # GymMembershipRepository, SubscriptionPlanRepository
│   │   └── UseCase/          # SubscribeToGym, RenewMembership, CancelMembership...
│   │
│   ├── Workout/
│   │   ├── Entity/           # WorkoutPlan, WorkoutExercise, WorkoutSession
│   │   ├── Repository/       # WorkoutPlanRepository, WorkoutExerciseRepository, WorkoutSessionRepository
│   │   └── UseCase/          # CreateWorkoutPlan, LogWorkoutSession, GetClientProgress...
│   │
│   ├── Medical/
│   │   ├── Entity/           # MedicalCertificate
│   │   ├── Repository/       # MedicalCertificateRepository
│   │   └── UseCase/          # UploadCertificate, ReviewCertificate, CheckValidity...
│   │
│   ├── Invitation/
│   │   ├── Entity/           # PTClientInvitation, GymPTInvitation
│   │   ├── Repository/       # PTClientInvitationRepository, GymPTInvitationRepository
│   │   └── UseCase/          # SendInvitation, AcceptInvitation, RejectInvitation...
│   │
│   └── Shared/
│       ├── ValueObject/      # Email, Money, DateRange...
│       ├── Exception/        # DomainException, ValidationException...
│       └── Service/          # Servizi condivisi tra domini
│
└── Application/              # Layer Applicativo (Orchestrazione)
    ├── Controller/           # HTTP Controllers
    ├── Form/                 # Symfony Forms
    └── Security/             # Voters, Guards, Authenticators
```

## 🎯 Principi Architetturali

### 1. **Separazione dei Concern**
- **Domain Layer**: Contiene la business logic pura, indipendente da framework
- **Application Layer**: Orchestrazione, HTTP, Forms, interfaccia utente
- **Infrastructure**: Doctrine, Symfony, librerie esterne

### 2. **Use Case Pattern**
Invece di mettere la logica nei Controller, creiamo **Use Case** dedicati:

```php
// ❌ EVITARE - Logica nel Controller
class MembershipController {
    public function subscribe(Request $request) {
        $user = $this->getUser();
        $membership = new GymMembership();
        $membership->setUser($user);
        $membership->setGym($gym);
        // ... 50 righe di logica ...
        $this->em->persist($membership);
        $this->em->flush();
    }
}

// ✅ PREFERIRE - Use Case dedicato
class SubscribeToGymUseCase {
    public function execute(SubscribeToGymCommand $command): GymMembership
    {
        // Validazioni business
        $this->validateSubscriptionRules($command);

        // Creazione membership
        $membership = $this->createMembership($command);

        // Persist
        $this->membershipRepository->save($membership);

        // Eventi
        $this->eventDispatcher->dispatch(new MembershipCreatedEvent($membership));

        return $membership;
    }
}

// Controller diventa thin
class MembershipController {
    public function subscribe(Request $request, SubscribeToGymUseCase $useCase) {
        $command = SubscribeToGymCommand::fromRequest($request);
        $membership = $useCase->execute($command);
        return $this->json(['id' => $membership->getId()]);
    }
}
```

### 3. **Repository Pattern**
I Repository sono l'unico punto di accesso ai dati del dominio:

```php
// Repository fornisce metodi semantici
$activeMemberships = $membershipRepo->findActiveByUser($user);
$expiringCerts = $certRepo->findExpiringCertificates();
```

### 4. **Dependency Injection**
Use Case ricevono dipendenze via constructor:

```php
class CreateWorkoutPlanUseCase {
    public function __construct(
        private WorkoutPlanRepository $planRepository,
        private PTClientRelationRepository $relationRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}
}
```

## 📚 Domini Definiti

### **User** - Gestione Utenti
Registrazione, autenticazione, profili, ruoli

### **Gym** - Gestione Palestre
Palestre, presenze, orari, servizi, PT interni

### **PersonalTrainer** - PT e Clienti
Profili PT (interni/esterni), relazioni PT-Cliente

### **Membership** - Abbonamenti
Piani sottoscrizione, abbonamenti, pagamenti, rinnovi

### **Workout** - Allenamenti
Schede workout, esercizi, sessioni, progressi

### **Medical** - Certificati Medici
Caricamento, validazione, scadenze certificati idoneità

### **Invitation** - Sistema Inviti
Inviti PT→Cliente e Palestra→PT con token

### **Shared** - Codice Condiviso
Value Objects, Exceptions, Services comuni

## 🔄 Flusso di una Request

```
1. HTTP Request
   ↓
2. Controller (Application Layer)
   - Valida input
   - Crea Command/DTO
   ↓
3. Use Case (Domain Layer)
   - Business Logic
   - Validazioni dominio
   - Orchestrazione Repository
   - Dispatch Events
   ↓
4. Repository (Domain Layer)
   - Persist/Retrieve Entity
   ↓
5. Controller
   - Formatta Response
   - Return JSON/HTML
```

## 📖 Come Implementare un Nuovo Use Case

1. **Definire il Command/DTO**
```php
// src/Domain/Membership/UseCase/SubscribeToGym/SubscribeToGymCommand.php
readonly class SubscribeToGymCommand {
    public function __construct(
        public int $userId,
        public int $gymId,
        public int $subscriptionPlanId,
        public \DateTimeInterface $startDate,
    ) {}
}
```

2. **Creare il Use Case**
```php
// src/Domain/Membership/UseCase/SubscribeToGym/SubscribeToGymUseCase.php
class SubscribeToGymUseCase {
    public function execute(SubscribeToGymCommand $command): GymMembership
    {
        // Implementazione...
    }
}
```

3. **Usare nel Controller**
```php
// src/Application/Controller/MembershipController.php
#[Route('/membership/subscribe', methods: ['POST'])]
public function subscribe(
    Request $request,
    SubscribeToGymUseCase $useCase
): JsonResponse {
    $command = new SubscribeToGymCommand(
        userId: $this->getUser()->getId(),
        gymId: $request->request->getInt('gymId'),
        subscriptionPlanId: $request->request->getInt('planId'),
        startDate: new \DateTime($request->request->get('startDate')),
    );

    $membership = $useCase->execute($command);

    return $this->json(['id' => $membership->getId()]);
}
```

## ✅ Benefici di questa Architettura

1. **Testabilità**: Use Case facilmente testabili in isolamento
2. **Manutenibilità**: Logica business separata da framework
3. **Scalabilità**: Facile aggiungere nuovi domini/use case
4. **Riusabilità**: Use Case riutilizzabili da Controller, CLI, Message Handlers
5. **Chiarezza**: Ogni Use Case ha responsabilità ben definita
6. **Team Work**: Team diversi possono lavorare su domini diversi

## 📝 Prossimi Step

Per completare l'implementazione:

1. ✅ Entity e Repository creati
2. ✅ Struttura DDD definita
3. ✅ Use Case documentati nei README di ogni dominio
4. ⏳ Implementare Use Case (iniziare da quelli critici)
5. ⏳ Creare Controller thin che usano Use Case
6. ⏳ Creare Form per input validation
7. ⏳ Creare Template Twig per UI

Consulta i README in ogni cartella `Domain/*/UseCase/` per la lista completa dei casi d'uso da implementare.
