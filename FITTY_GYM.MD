# FITTY GYM - Applicazione per la Gestione delle Palestre

## Descrizione
Applicazione completa per la gestione delle palestre che gestisce abbonamenti, visite mediche, personal trainer (interni ed esterni) e schede di allenamento.

## Flusso Applicativo

### Ruoli Utente
- **ROLE_USER**: Utente/Cliente
- **ROLE_PT**: Personal Trainer (può essere interno o esterno alla palestra)
- **ROLE_GYM_ADMIN**: Amministratore della palestra
- **ROLE_SUPER_ADMIN**: Super amministratore (gestione multi-palestra)

---

## Scenari Principali

### Scenario 1: Utente NON iscritto alla palestra
- L'utente si registra nel sistema
- **NON ha** abbonamento attivo
- **NON ha** accesso fisico alla palestra
- **PUÒ** essere seguito da un PT esterno (freelance)
- Il PT esterno può creargli schede di allenamento
- L'utente può visualizzare e seguire le sue schede

### Scenario 2: Utente iscritto alla palestra
- L'utente si iscrive alla palestra (abbonamento attivo)
- Carica/completa la visita medica
- **HA** accesso fisico alla palestra
- **PUÒ** scegliere tra:
  - **Opzione A**: Allenarsi in autonomia (nessun PT)
  - **Opzione B**: Essere seguito da un PT interno della palestra
  - **Opzione C**: Essere seguito da un PT esterno (che gli crea schede)
- Può avere una o più schede attive

### Scenario 3: Personal Trainer Esterno (Freelance)
- Si registra come PT nel sistema
- **NON** è associato a nessuna palestra
- Può invitare clienti e creare schede per loro
- I suoi clienti possono essere iscritti o meno a palestre
- Gestisce il proprio portfolio clienti in autonomia

### Scenario 4: Personal Trainer Interno
- È un dipendente/collaboratore della palestra
- Viene invitato dalla palestra e associato ad essa
- Può seguire solo clienti iscritti alla sua palestra
- Ha accesso alla dashboard della palestra

### Scenario 5: Palestra (Admin)
- Gestisce abbonamenti dei clienti
- Gestisce visite mediche
- Invita e gestisce PT interni
- Visualizza statistiche e presenze
- Gestisce i piani abbonamento disponibili

---

## Implementazione Tecnica

### Punto 1: Entity e Database

#### 1.1 Entity User
- id
- email
- password
- roles (array: può avere più ruoli contemporaneamente)
- firstName
- lastName
- dateOfBirth
- gender
- phoneNumber
- address
- city
- postalCode
- profileImage
- createdAt
- updatedAt
- Relazioni:
  - gymMemberships (OneToMany con GymMembership)
  - medicalCertificates (OneToMany con MedicalCertificate)
  - personalTrainerProfile (OneToOne con PersonalTrainer) - se è un PT
  - workoutPlans (OneToMany con WorkoutPlan) - schede assegnate

#### 1.2 Entity Gym
- id
- name
- description
- address
- city
- postalCode
- province
- phoneNumber
- email
- website
- vatNumber
- logo
- openingHours (json)
- amenities (array) - servizi disponibili
- isActive
- createdAt
- updatedAt
- Relazioni:
  - admins (ManyToMany con User) - amministratori
  - internalPTs (OneToMany con PersonalTrainer) - PT interni
  - memberships (OneToMany con GymMembership)
  - subscriptionPlans (OneToMany con SubscriptionPlan)

#### 1.3 Entity PersonalTrainer
- id
- user (OneToOne con User)
- gym (ManyToOne con Gym, nullable) - NULL se PT esterno
- isInternal (boolean) - true se PT interno, false se esterno
- specialization (string)
- certifications (array)
- biography (text)
- hourlyRate (decimal, nullable)
- experience (integer) - anni esperienza
- isActive
- isAvailableForNewClients
- createdAt
- updatedAt
- Relazioni:
  - clients (ManyToMany con User attraverso PTClientRelation)
  - workoutPlans (OneToMany con WorkoutPlan)

#### 1.4 Entity PTClientRelation
- id
- personalTrainer (ManyToOne con PersonalTrainer)
- client (ManyToOne con User)
- status (pending, active, suspended, terminated)
- startDate
- endDate (nullable)
- notes (text)
- monthlyFee (decimal, nullable) - se gestito nel sistema
- createdAt
- updatedAt

#### 1.5 Entity PTClientInvitation
- id
- personalTrainer (ManyToOne con PersonalTrainer)
- clientEmail (string)
- clientUser (ManyToOne con User, nullable) - se già registrato
- token (string, univoco)
- status (pending, accepted, rejected, expired)
- message (text)
- invitedAt
- respondedAt
- expiresAt

#### 1.6 Entity GymMembership
- id
- gym (ManyToOne con Gym)
- user (ManyToOne con User)
- subscriptionPlan (ManyToOne con SubscriptionPlan)
- status (pending, active, suspended, expired, cancelled)
- startDate
- endDate
- autoRenew (boolean)
- price (decimal)
- paymentMethod (string)
- lastPaymentDate
- nextPaymentDate
- assignedPT (ManyToOne con PersonalTrainer, nullable)
- notes (text)
- createdAt
- updatedAt
- Relazioni:
  - medicalCertificate (OneToOne con MedicalCertificate)
  - attendances (OneToMany con GymAttendance)

#### 1.7 Entity SubscriptionPlan
- id
- gym (ManyToOne con Gym)
- name
- description
- duration (integer) - giorni
- price (decimal)
- includePT (boolean) - include PT
- ptSessionsIncluded (integer)
- maxAccessPerWeek (integer, nullable)
- features (array) - lista vantaggi
- isActive
- createdAt
- updatedAt

#### 1.8 Entity MedicalCertificate
- id
- user (ManyToOne con User)
- gymMembership (OneToOne con GymMembership, nullable)
- certificateType (agonistico, non_agonistico)
- issueDate
- expiryDate
- doctorName
- doctorNumber
- filePath (string) - path del PDF/immagine
- status (pending_review, approved, rejected, expired)
- notes
- uploadedAt
- reviewedAt
- reviewedBy (ManyToOne con User, nullable)

#### 1.9 Entity GymAttendance
- id
- gym (ManyToOne con Gym)
- user (ManyToOne con User)
- gymMembership (ManyToOne con GymMembership)
- checkInTime
- checkOutTime (nullable)
- duration (integer, nullable) - minuti
- notes
- createdAt

#### 1.10 Entity WorkoutPlan
- id
- name
- description
- personalTrainer (ManyToOne con PersonalTrainer)
- client (ManyToOne con User)
- planType (strength, cardio, hybrid, recovery, custom)
- goal (text)
- startDate
- endDate (nullable)
- weeksCount (integer)
- isActive
- isTemplate (boolean) - se è un template riutilizzabile
- notes (text)
- createdAt
- updatedAt
- Relazioni:
  - exercises (OneToMany con WorkoutExercise)
  - sessions (OneToMany con WorkoutSession)

#### 1.11 Entity WorkoutExercise
- id
- workoutPlan (ManyToOne con WorkoutPlan)
- dayNumber (integer) - giorno della settimana (1-7)
- dayLabel (string) - es. "Giorno 1 - Petto/Tricipiti"
- orderPosition (integer)
- exerciseName
- exerciseCategory (strength, cardio, flexibility, warmup, cooldown)
- muscleGroup (chest, back, shoulders, arms, legs, core, full_body)
- sets (integer)
- reps (string) - può essere "12" o "8-12" o "AMRAP"
- weight (decimal, nullable)
- restTime (integer) - secondi
- tempo (string, nullable) - es. "3-1-1-1"
- rpe (integer, nullable) - Rate of Perceived Exertion (1-10)
- notes (text)
- videoUrl (string, nullable)
- createdAt
- updatedAt

#### 1.12 Entity WorkoutSession
- id
- workoutPlan (ManyToOne con WorkoutPlan)
- client (ManyToOne con User)
- personalTrainer (ManyToOne con PersonalTrainer, nullable)
- sessionDate
- startTime
- endTime
- duration (integer) - minuti
- completedExercises (json) - dettagli esercizi completati
- bodyWeight (decimal, nullable)
- mood (string, nullable) - ottimo, buono, normale, stanco
- energyLevel (integer, nullable) - 1-10
- notes (text)
- rating (integer, nullable) - 1-5
- createdAt
- updatedAt

#### 1.13 Entity GymPTInvitation
- id
- gym (ManyToOne con Gym)
- invitedEmail (string)
- invitedUser (ManyToOne con User, nullable)
- token (string, univoco)
- status (pending, accepted, rejected, expired)
- message (text)
- invitedBy (ManyToOne con User)
- invitedAt
- respondedAt
- expiresAt

---

### Punto 2: Sistema di Sicurezza

#### 2.1 Configurazione Security (config/packages/security.yaml)
- Firewall per area pubblica e area privata
- Password hasher con algoritmo auto
- Access control:
  - `/gym/admin/*` → ROLE_GYM_ADMIN
  - `/pt/dashboard/*` → ROLE_PT
  - `/client/*` → ROLE_USER (autenticato)
  - `/public/*` → pubblico

#### 2.2 Voters personalizzati
- **GymAccessVoter**: Verifica che l'admin possa gestire solo la propria palestra
- **PTClientAccessVoter**: Verifica che il PT possa accedere solo ai propri clienti
- **WorkoutPlanVoter**: Verifica che solo il PT creatore o il cliente possano vedere la scheda
- **MembershipVoter**: Verifica accesso ai dati abbonamento
- **MedicalCertificateVoter**: Verifica accesso ai certificati medici

---

### Punto 3: Controller

#### 3.1 GymAdminController (ROLE_GYM_ADMIN)
- `index()`: Dashboard palestra con KPI
- `members()`: Gestione iscritti
- `memberDetail($userId)`: Dettaglio iscritto
- `addMember()`: Aggiungi nuovo iscritto
- `editMembership($membershipId)`: Modifica abbonamento
- `invitePT()`: Invita PT interno
- `listPTs()`: Lista PT interni
- `removePT($ptId)`: Rimuove PT
- `subscriptionPlans()`: Gestione piani abbonamento
- `medicalCertificates()`: Revisione certificati medici
- `attendances()`: Storico presenze
- `settings()`: Impostazioni palestra

#### 3.2 PTDashboardController (ROLE_PT)
- `index()`: Dashboard PT
- `clients()`: Lista clienti
- `clientDetail($clientId)`: Dettaglio cliente
- `inviteClient()`: Invita nuovo cliente
- `myInvitations()`: Lista inviti inviati
- `clientProgress($clientId)`: Progressi cliente

#### 3.3 WorkoutPlanController (ROLE_PT)
- `index()`: Lista schede create
- `create($clientId)`: Crea nuova scheda
- `edit($planId)`: Modifica scheda
- `view($planId)`: Visualizza scheda
- `duplicate($planId)`: Duplica scheda
- `delete($planId)`: Elimina scheda
- `templates()`: Gestione template schede
- `assignToClient($planId, $clientId)`: Assegna scheda a cliente

#### 3.4 ClientController (ROLE_USER)
- `dashboard()`: Dashboard personale
- `myWorkoutPlans()`: Le mie schede
- `viewPlan($planId)`: Visualizza scheda dettaglio
- `logSession($planId)`: Registra sessione allenamento
- `myProgress()`: I miei progressi
- `myMemberships()`: I miei abbonamenti palestre
- `uploadMedicalCertificate($membershipId)`: Carica certificato medico
- `myPTs()`: I miei personal trainer
- `findPT()`: Cerca PT disponibili

#### 3.5 InvitationController (pubblico/autenticato)
- `acceptPTInvitation($token)`: Accetta invito PT
- `rejectPTInvitation($token)`: Rifiuta invito PT
- `acceptGymInvitation($token)`: Accetta invito palestra (per PT)
- `rejectGymInvitation($token)`: Rifiuta invito palestra

#### 3.6 PublicController (pubblico)
- `home()`: Homepage
- `gymDirectory()`: Lista palestre pubbliche
- `gymDetail($gymId)`: Dettaglio palestra
- `ptDirectory()`: Lista PT esterni disponibili
- `ptDetail($ptId)`: Dettaglio PT
- `register()`: Registrazione
- `login()`: Login

#### 3.7 MembershipController (ROLE_USER)
- `subscribe($gymId, $planId)`: Sottoscrivi abbonamento
- `cancelMembership($membershipId)`: Cancella abbonamento
- `renewMembership($membershipId)`: Rinnova abbonamento
- `choosePT($membershipId)`: Scegli PT interno

---

### Punto 4: Form

#### 4.1 GymMembershipFormType
- subscriptionPlan (choice)
- startDate
- assignedPT (choice, opzionale)
- autoRenew (checkbox)
- paymentMethod (choice)
- notes

#### 4.2 MedicalCertificateFormType
- certificateType (choice)
- issueDate
- expiryDate
- doctorName
- doctorNumber
- certificateFile (file upload)

#### 4.3 PTInvitationFormType
- clientEmail
- message (textarea)

#### 4.4 WorkoutPlanFormType
- name
- description
- planType (choice)
- goal
- startDate
- endDate
- weeksCount
- isTemplate (checkbox)
- exercises (CollectionType)

#### 4.5 WorkoutExerciseFormType
- dayNumber
- dayLabel
- exerciseName
- exerciseCategory
- muscleGroup
- sets, reps, weight
- restTime
- tempo, rpe
- notes, videoUrl

#### 4.6 WorkoutSessionFormType
- sessionDate
- startTime, endTime
- completedExercises (json)
- bodyWeight
- mood, energyLevel
- notes, rating

#### 4.7 SubscriptionPlanFormType
- name, description
- duration, price
- includePT
- ptSessionsIncluded
- maxAccessPerWeek
- features (collection)

---

### Punto 5: Template Twig

#### 5.1 Layout base
- `templates/base.html.twig`: Layout principale
- `templates/components/navbar.html.twig`: Navbar dinamica per ruolo
- `templates/components/sidebar.html.twig`: Sidebar dinamica

#### 5.2 Public
- `templates/public/home.html.twig`: Homepage
- `templates/public/gym_directory.html.twig`: Lista palestre
- `templates/public/pt_directory.html.twig`: Lista PT

#### 5.3 Dashboard Admin Palestra
- `templates/gym/admin/index.html.twig`: Dashboard
- `templates/gym/admin/members.html.twig`: Gestione iscritti
- `templates/gym/admin/member_detail.html.twig`: Dettaglio iscritto
- `templates/gym/admin/pts.html.twig`: Gestione PT
- `templates/gym/admin/subscription_plans.html.twig`: Piani abbonamento
- `templates/gym/admin/medical_certificates.html.twig`: Certificati medici
- `templates/gym/admin/attendances.html.twig`: Presenze

#### 5.4 Dashboard PT
- `templates/pt/dashboard/index.html.twig`: Dashboard PT
- `templates/pt/clients/list.html.twig`: Lista clienti
- `templates/pt/clients/detail.html.twig`: Dettaglio cliente
- `templates/pt/clients/invite.html.twig`: Invita cliente
- `templates/pt/workout_plans/list.html.twig`: Lista schede
- `templates/pt/workout_plans/create.html.twig`: Crea scheda
- `templates/pt/workout_plans/edit.html.twig`: Modifica scheda
- `templates/pt/workout_plans/view.html.twig`: Visualizza scheda

#### 5.5 Area Cliente
- `templates/client/dashboard.html.twig`: Dashboard cliente
- `templates/client/workout_plans.html.twig`: Le mie schede
- `templates/client/workout_plan_detail.html.twig`: Dettaglio scheda
- `templates/client/log_session.html.twig`: Registra allenamento
- `templates/client/progress.html.twig`: Progressi
- `templates/client/memberships.html.twig`: Abbonamenti
- `templates/client/my_pts.html.twig`: Miei PT
- `templates/client/find_pt.html.twig`: Cerca PT

#### 5.6 Inviti
- `templates/invitation/accept_pt.html.twig`: Accetta invito PT
- `templates/invitation/accept_gym.html.twig`: Accetta invito palestra

---

### Punto 6: Repository e Servizi

#### 6.1 Repository
- UserRepository
- GymRepository
- PersonalTrainerRepository
- GymMembershipRepository
- MedicalCertificateRepository
- WorkoutPlanRepository
- WorkoutSessionRepository
- PTClientRelationRepository
- GymAttendanceRepository
- SubscriptionPlanRepository

#### 6.2 Servizi
- **MembershipService**: Gestione abbonamenti (sottoscrizione, rinnovo, cancellazione)
- **MedicalCertificateService**: Validazione e gestione certificati
- **PTInvitationService**: Gestione inviti clienti
- **GymInvitationService**: Gestione inviti PT
- **WorkoutPlanService**: Creazione e gestione schede
- **WorkoutSessionService**: Registrazione allenamenti e statistiche
- **AttendanceService**: Gestione presenze palestra
- **NotificationService**: Invio notifiche (email/SMS)
- **PaymentService**: Gestione pagamenti abbonamenti (integrazione futura)
- **ReportService**: Generazione report e statistiche

---

### Punto 7: Features Avanzate (Opzionali - Fasi successive)

#### 7.1 Gestione Pagamenti
- Integrazione Stripe/PayPal
- Pagamenti ricorrenti automatici
- Fatturazione elettronica
- Storico transazioni

#### 7.2 Sistema Check-in/Check-out
- QR code per accesso palestra
- Rilevamento presenze automatico
- Controllo validità abbonamento e visita medica
- Notifiche scadenze

#### 7.3 Analytics e Reports
- Dashboard con grafici progressi
- Report presenze
- Report incassi
- Statistiche utilizzo palestra
- Export dati (PDF, Excel)

#### 7.4 Biblioteca Esercizi
- Database esercizi predefiniti
- Video tutorial
- Filtri per categoria/muscolo
- Possibilità di aggiungere esercizi custom

#### 7.5 Sistema Notifiche
- Email scadenza abbonamento
- Email scadenza visita medica
- Reminder allenamenti
- Notifiche nuove schede assegnate

#### 7.6 Marketplace PT
- Directory pubblica PT
- Recensioni e rating
- Filtri per specializzazione/zona
- Prenotazione consulenze

#### 7.7 App Mobile
- Progressive Web App (PWA)
- Notifiche push
- Offline mode per visualizzare schede
- Quick log allenamenti

---

## Note di Implementazione

### Priorità Sviluppo

**Fase 1 - MVP (Core)**
1. Sistema autenticazione e ruoli
2. Entity User, Gym, PersonalTrainer, PTClientRelation
3. Entity GymMembership, SubscriptionPlan
4. Entity WorkoutPlan, WorkoutExercise, WorkoutSession
5. Controller base per Admin Palestra, PT, Cliente
6. Form e template essenziali
7. Sistema inviti PT → Cliente

**Fase 2 - Gestione Completa**
1. Entity MedicalCertificate
2. Upload e validazione certificati
3. Sistema inviti Palestra → PT
4. Gestione presenze (GymAttendance)
5. Dashboard e statistiche base
6. Ricerca PT pubblici

**Fase 3 - Features Avanzate**
1. Sistema pagamenti
2. Check-in automatico
3. Analytics avanzate
4. Biblioteca esercizi
5. Notifiche automatiche
6. Marketplace PT
7. PWA

### Flussi Chiave da Implementare

1. **Registrazione Utente → Iscrizione Palestra**
   - Utente si registra
   - Sceglie palestra e piano abbonamento
   - Carica visita medica
   - Admin approva → abbonamento attivo

2. **PT Esterno → Invita Cliente**
   - PT invia invito via email
   - Cliente accetta e si registra (se nuovo)
   - Relazione PT-Cliente creata
   - PT può creare schede

3. **Palestra → Invita PT Interno**
   - Admin invia invito PT
   - PT accetta
   - PersonalTrainer creato con gym_id
   - PT può seguire clienti iscritti

4. **Cliente → Sceglie PT Interno**
   - Cliente con abbonamento attivo
   - Sceglie PT dalla lista
   - Relazione creata
   - PT notificato

### Tecnologie
- Symfony 7.3
- Doctrine ORM
- Twig
- Stimulus (interattività frontend)
- Turbo (SPA-like navigation)
- Chart.js (grafici)
- Symfony Messenger (job asincroni)
- Symfony Mailer (notifiche email)

### Sicurezza
- Autenticazione robusta
- Voters per controllo accessi granulare
- Validazione input completa
- CSRF protection
- Rate limiting su API critiche
- Crittografia dati sensibili (certificati medici)
- GDPR compliance