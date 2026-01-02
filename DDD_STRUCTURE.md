# 📐 Architettura DDD - FittyHub

## ✅ Struttura Vertical Slice Implementata

### 📁 Struttura Generale (Organizzata per Modulo)

```
src/
├── Domain/                          # Organizzazione per MODULO (Vertical Slice)
│   ├── Membership/
│   │   ├── Entity/                 # Entità del dominio
│   │   ├── Repository/             # Interfacce repository
│   │   ├── UseCase/                # Use Cases del modulo
│   │   ├── Service/                # Service applicativi (se necessari)
│   │   └── State/                  # State processors (API Platform)
│   │
│   ├── Course/
│   │   ├── Entity/
│   │   ├── Repository/
│   │   ├── UseCase/
│   │   └── Service/
│   │
│   ├── PersonalTrainer/
│   │   ├── Entity/
│   │   ├── Repository/
│   │   ├── UseCase/
│   │   └── Service/
│   │
│   ├── Medical/                    # Certificati medici
│   │   ├── Entity/
│   │   ├── Repository/
│   │   ├── UseCase/
│   │   └── Service/
│   │
│   ├── Gym/                        # Check-in e presenze
│   │   ├── Entity/
│   │   ├── Repository/
│   │   ├── UseCase/
│   │   └── Service/
│   │
│   ├── Invitation/
│   │   ├── Entity/
│   │   ├── Repository/
│   │   ├── UseCase/
│   │   └── Service/
│   │
│   └── User/
│       ├── Entity/
│       ├── Repository/
│       ├── UseCase/
│       └── Service/
│
├── Infrastructure/Persistence/      # Implementazioni tecniche (Doctrine)
│   └── Doctrine/Repository/
│       ├── DoctrineMembershipRepository.php
│       ├── DoctrineCourseRepository.php
│       └── ...
│
└── Controller/Admin/                # HTTP Layer
    ├── MembershipController.php
    ├── CourseController.php
    └── ...
```

### 🎯 Vantaggi Vertical Slice

- ✅ **Tutto relativo a un modulo sta insieme** (facile trovare codice)
- ✅ **Zero cartelle duplicate** (era: Application/UseCase/Membership + Domain/Membership)
- ✅ **Più semplice da capire** (1 cartella = 1 modulo completo)
- ✅ **Facile aggiungere nuovi moduli** (basta copiare la struttura)

---

## 🎯 Moduli Implementati

### ✅ 1. Membership (Completo - Riferimento)

**Domain:**
- ✓ `MembershipRepositoryInterface`
- ✓ `SubscriptionPlanRepositoryInterface`
- ✓ `EnrollmentRepositoryInterface`

**Infrastructure:**
- ✓ `DoctrineMembershipRepository`
- ✓ `DoctrineSubscriptionPlanRepository`
- ✓ `DoctrineEnrollmentRepository`

**Use Cases - Membership:**
- ✓ `GetMembershipById`
- ✓ `SearchMemberships`
- ✓ `CancelMembership`
- ✓ `RenewMembership`
- ✓ `GetMembershipStats`
- ✓ `GetExpiringMemberships`
- ✓ `ReactivateMembership`
- ✓ `UpdateMembershipAndUser`

**Use Cases - Enrollment (Quote Iscrizione):**
- ✓ `GetAllEnrollments`
- ✓ `GetExpiringEnrollments`
- ✓ `GetEnrollmentById`
- ✓ `GetUserEnrollmentHistory`
- ✓ `CreateEnrollment`
- ✓ `ExpireEnrollment`

**Use Cases - Subscription Plans:**
- ✓ `GetAllSubscriptionPlans`
- ✓ `GetSubscriptionPlanById`
- ✓ `CreateSubscriptionPlan`
- ✓ `UpdateSubscriptionPlan`
- ✓ `ToggleSubscriptionPlan`
- ✓ `DeleteSubscriptionPlan`

**Controllers:**
- ✓ `MembershipController` (aggiornato con Use Cases)
- ✓ `EnrollmentController` (aggiornato con Use Cases)
- ✓ `SubscriptionPlanController` (aggiornato con Use Cases)

---

### ✅ 2. Course (Completo)

**Domain:**
- ✓ `CourseRepositoryInterface`
- ✓ `CourseScheduleRepositoryInterface`
- ✓ `CourseEnrollmentRepositoryInterface`
- ✓ `CourseCategoryRepositoryInterface`

**Infrastructure:**
- ✓ `DoctrineCourseRepository`
- ✓ `DoctrineCourseScheduleRepository`
- ✓ `DoctrineCourseEnrollmentRepository`
- ✓ `DoctrineCourseCategoryRepository`

**Use Cases:**
- ✓ `GetCourseById`
- ✓ `SearchCourses`
- ✓ `GetCourseStats`
- ✓ `GetScheduleById`
- ✓ `GetEnrollmentById`

**Controller:**
- ✓ `CourseController` (aggiornato con Use Cases)

---

### ✅ 3. Trainer (Completo)

**Domain:**
- ✓ `TrainerRepositoryInterface`
- ✓ `PTClientRelationRepositoryInterface`

**Infrastructure:**
- ✓ `DoctrineTrainerRepository`
- ✓ `DoctrinePTClientRelationRepository`

**Use Cases:**
- ✓ `GetTrainerById`
- ✓ `SearchTrainers`
- ✓ `AssignTrainerToClient`

**Controller:**
- ✓ `TrainerController` (aggiornato con Use Cases)

---

### ✅ 4. Certificate (Completo)

**Domain:**
- ✓ `MedicalCertificateRepositoryInterface`

**Infrastructure:**
- ✓ `DoctrineMedicalCertificateRepository`

**Use Cases:**
- ✓ `GetCertificateById`
- ✓ `SearchCertificates`
- ✓ `GetCertificateStats`
- ✓ `ApproveCertificate`
- ✓ `RejectCertificate`
- ✓ `UploadCertificate`

**Controller:**
- ✓ `CertificateController` (aggiornato con Use Cases)

---

### ✅ 5. Invitation (Completo)

**Domain:**
- ✓ `InvitationRepositoryInterface`

**Infrastructure:**
- ✓ `DoctrineInvitationRepository`

**Use Cases:**
- ✓ `GetInvitationById`
- ✓ `SearchInvitations`
- ✓ `GetInvitationStats`
- ✓ `CreateInvitation`
- ✓ `ResendInvitation`
- ✓ `CancelInvitation`

**Controller:**
- ✓ `InvitationController` (aggiornato con Use Cases)

---

### ✅ 6. User (Completo)

**Domain:**
- ✓ `UserRepositoryInterface`

**Infrastructure:**
- ✓ `DoctrineUserRepository`

---

### ✅ 7. Gym/CheckIn (Completo)

**Domain:**
- ✓ `GymAttendanceRepositoryInterface`

**Infrastructure:**
- ✓ `DoctrineGymAttendanceRepository`

**Use Cases:**
- ✓ `ValidateCheckIn`
- ✓ `ProcessCheckIn`
- ✓ `GetUserAttendanceHistory`
- ✓ `GetAttendanceStats`
- ✓ `GetRecentAttendances`

**Controller:**
- ✓ `CheckInController` (aggiornato con Use Cases)

---

## 🔗 Configurazione (services.yaml)

Tutti i binding sono configurati:

```yaml
# Membership
App\Domain\Membership\Repository\MembershipRepositoryInterface:
    alias: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineMembershipRepository

# Course
App\Domain\Course\Repository\CourseRepositoryInterface:
    alias: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineCourseRepository

# Trainer
App\Domain\PersonalTrainer\Repository\TrainerRepositoryInterface:
    alias: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineTrainerRepository

# Medical
App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface:
    alias: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineMedicalCertificateRepository

# Invitation
App\Domain\Invitation\Repository\InvitationRepositoryInterface:
    alias: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineInvitationRepository

# User
App\Domain\User\Repository\UserRepositoryInterface:
    alias: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserRepository

# Gym
App\Domain\Gym\Repository\GymAttendanceRepositoryInterface:
    alias: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineGymAttendanceRepository
```

---

## 📚 Come Funziona

### Prima (❌ Male)

```php
// Controller con troppa logica
class MembershipController {
    public function __construct(
        private GymMembershipRepository $repo,  // ❌ Implementazione concreta
        private EntityManager $em              // ❌ Dettaglio tecnico
    ) {}

    public function renew(int $id) {
        $membership = $this->repo->find($id);   // ❌ Query diretta
        // ... 50 righe di logica business ...
        $this->em->persist($new);               // ❌ Persistenza nel controller
        $this->em->flush();
    }
}
```

### Dopo (✅ Bene)

```php
// Controller pulito con Use Case
class MembershipController {
    public function __construct(
        private GetMembershipById $getMembership,    // ✅ Use Case
        private RenewMembership $renewMembership     // ✅ Use Case
    ) {}

    public function renew(int $id, Request $request) {
        $membership = $this->getMembership->execute($id);
        
        $newMembership = $this->renewMembership->execute(
            $membership,
            $plan,
            actualPrice: 50.0,
            bonusMonths: 1
        );
        
        // ✅ Controller = 5 righe chiare!
    }
}
```

---

## 🎓 Best Practices

### 1. **Organizzazione Vertical Slice (per modulo)**

Ogni modulo in `Domain/` contiene **tutto** quello che serve:
```
Domain/Membership/
  ├── Entity/              ← Entità del dominio
  ├── Repository/          ← Interfacce repository
  ├── UseCase/             ← Use Cases (business logic)
  └── Service/             ← Service applicativi (se necessari)
```

**Vantaggi:**
- ✅ Tutto relativo a "Membership" sta in 1 cartella
- ✅ Non devi saltare tra Domain/ e Application/
- ✅ Nuovo dev trova subito tutto

### 2. **Separazione Layer**

- **Domain/{Modulo}** = regole business (NO Symfony, NO Doctrine)
  - Contiene: Entity, Repository (interfacce), UseCase, Service
  - Usano **solo interfacce**, mai implementazioni Doctrine
- **Infrastructure** = implementazioni tecniche (Doctrine, file, API)
  - Contiene le **implementazioni** dei Repository
  - **Le query SQL/DQL stanno QUI**, non in QueryService!
- **Controller** = solo HTTP
  - Orchestrano Use Cases, niente di più

### 3. **Naming e Namespace**

```php
// ✅ GIUSTO - Namespace riflette il modulo
namespace App\Domain\Membership\UseCase;
class RenewMembership { ... }

namespace App\Domain\Course\UseCase;
class GetCourseById { ... }

// Import diretto dal modulo
use App\Domain\Membership\UseCase\RenewMembership;
use App\Domain\Course\UseCase\GetCourseById;
```

- ✅ `RenewMembership` (verbo + sostantivo)
- ❌ `MembershipRenewer` (sostantivo)
- ✅ `GetCourseById` (chiaro)
- ❌ `CourseService::get()` (generico)

### 4. **Use Case = 1 Azione**

```php
// ✅ Giusto
class RenewMembership { ... }
class CancelMembership { ... }
class SearchMemberships { ... }

// ❌ Sbagliato
class MembershipService {
    public function renew() { ... }
    public function cancel() { ... }
    public function search() { ... }
}
```

### 5. **Interfacce vs Implementazioni**

```php
// ✅ Domain usa INTERFACCIA
class RenewMembership {
    public function __construct(
        private MembershipRepositoryInterface $repo  // ✅
    ) {}
}

// ✅ Infrastructure implementa
class DoctrineMembershipRepository implements MembershipRepositoryInterface {
    // Dettagli Doctrine qui
}

// ✅ Symfony fa il binding automatico
```

### 6. **Query nei Repository (NO QueryService!)**

```php
// ❌ SBAGLIATO - QueryService è un livello inutile
class CertificateQueryService {
    public function searchCertificates($status) {
        return $this->repo->findWithFilters($status); // wrapper inutile!
    }
}

// ✅ GIUSTO - Query direttamente nel Repository
interface MedicalCertificateRepositoryInterface {
    public function findWithFilters(?string $status, ?string $search): array;
}

class DoctrineMedicalCertificateRepository {
    public function findWithFilters(?string $status, ?string $search): array {
        // Query Doctrine QUI nel repository
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }
}

// Use Case usa direttamente il repository
class SearchCertificates {
    public function execute($status) {
        return $this->certificateRepository->findWithFilters($status);
    }
}
```

### 7. **Testing**

```php
// ✅ Facile testare
$mockRepo = $this->createMock(MembershipRepositoryInterface::class);
$useCase = new RenewMembership($mockRepo);

// ❌ Difficile testare
$controller = new MembershipController($em, $repo, ...); // troppi mock!
```

---

## 🚀 Vantaggi Ottenuti

1. ✅ **Codice più leggibile**: 1 Use Case = 1 azione chiara
2. ✅ **Testing facile**: Mock solo interfacce
3. ✅ **Manutenzione**: cambio database? Solo Infrastructure!
4. ✅ **Onboarding**: nuovo dev capisce subito Use Cases
5. ✅ **Scalabilità**: aggiungi funzionalità senza toccare esistenti
6. ✅ **Query nei Repository**: zero livelli inutili (QueryService eliminati)
7. ✅ **Separation of Concerns**: ogni layer ha responsabilità chiare
8. ✅ **Vertical Slice**: tutto relativo a un modulo in 1 cartella
9. ✅ **Zero cartelle duplicate**: eliminata Application/, tutto in Domain/

---

## 📋 Prossimi Step

1. ✅ Tutte le interfacce create
2. ✅ Tutte le implementazioni create
3. ✅ Use Cases principali creati
4. ✅ Binding configurato
5. ✅ Controller aggiornati con Use Cases
6. ✅ Tutti i controller principali (Membership, Enrollment, SubscriptionPlan, Course, Trainer, Certificate, Invitation, CheckIn) ora usano Use Cases
7. ✅ **Riorganizzazione Vertical Slice completata** (Application/ eliminata, tutto in Domain/)
8. ✅ **QueryService eliminati** (query nei Repository dove devono stare)
9. ✅ **Tutti i controller del modulo Membership completati** (Membership, Enrollment, SubscriptionPlan)
10. ⏳ Scrivere test per Use Cases
11. ⏳ Aggiungere Use Cases per controller rimanenti (Dashboard, etc.)

---

## 💡 Esempio Pratico Completo

### Struttura File (Vertical Slice)

```
src/
├── Domain/Membership/                               ← TUTTO qui!
│   ├── Entity/GymMembership.php                    ← Entità
│   ├── Repository/MembershipRepositoryInterface.php ← Contratto
│   └── UseCase/RenewMembership.php                 ← Business logic
│
├── Infrastructure/Persistence/Doctrine/Repository/
│   └── DoctrineMembershipRepository.php            ← Implementazione Doctrine
│
└── Controller/Admin/
    └── MembershipController.php                     ← HTTP layer
```

### Flusso Richiesta

```
1. HTTP Request
   ↓
2. Controller (prende parametri)
   ↓
3. Use Case (esegue business logic)
   ↓
4. Repository Interface (chiede dati)
   ↓
5. Infrastructure Implementation (query Doctrine)
   ↓
6. Domain Entity (ritorna entità)
   ↓
7. Controller (renderizza risposta)
```

---

## 📊 Riepilogo Use Cases Creati

### Membership (22 Use Cases)
- **Membership (8)**: GetMembershipById, SearchMemberships, CancelMembership, RenewMembership, GetMembershipStats, GetExpiringMemberships, ReactivateMembership, UpdateMembershipAndUser
- **Enrollment (6)**: GetAllEnrollments, GetExpiringEnrollments, GetEnrollmentById, GetUserEnrollmentHistory, CreateEnrollment, ExpireEnrollment
- **Subscription Plans (6)**: GetAllSubscriptionPlans, GetSubscriptionPlanById, CreateSubscriptionPlan, UpdateSubscriptionPlan, ToggleSubscriptionPlan, DeleteSubscriptionPlan
- **Trainer Assignment (2)**: AssignTrainerToClient, GetPTClientRelations

### Course (5 Use Cases)
- GetCourseById, SearchCourses, GetCourseStats, GetScheduleById, GetEnrollmentById

### Trainer (3 Use Cases)
- GetTrainerById, SearchTrainers, AssignTrainerToClient

### Certificate (6 Use Cases)
- GetCertificateById, SearchCertificates, GetCertificateStats
- ApproveCertificate, RejectCertificate, UploadCertificate

### Invitation (6 Use Cases)
- GetInvitationById, SearchInvitations, GetInvitationStats
- CreateInvitation, ResendInvitation, CancelInvitation

### CheckIn (5 Use Cases)
- ValidateCheckIn, ProcessCheckIn, GetUserAttendanceHistory
- GetAttendanceStats, GetRecentAttendances

**Totale: 47 Use Cases implementati** ✅

---

## 🎉 Risultato Finale

**Codice:**
- ✅ Più chiaro
- ✅ Più testabile
- ✅ Più manutenibile
- ✅ Più scalabile

**Controller:**
- ✅ Tutti i controller principali aggiornati
- ✅ Nessun TODO rimasto
- ✅ Solo orchestrazione HTTP, zero business logic
- ✅ Media 5-10 righe per metodo

**Architettura:**
- ✅ DDD completo su 7 moduli
- ✅ Clean Architecture applicata
- ✅ Dependency Injection configurata
- ✅ Repository Pattern su tutte le entità

**Team:**
- ✅ Capisce velocemente
- ✅ Aggiunge features facilmente
- ✅ Trova bug rapidamente

**FittyHub:**
- ✅ Pronto per crescere! 🚀
