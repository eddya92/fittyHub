# FITTY HUB - Piattaforma Fitness & Nutrizione

## 📋 Indice Documentazione

- **FITTY_HUB.MD** (questo file): Visione strategica, business model, architettura core
- **FITTY_GYM.MD**: Modulo fitness - entity, controller, form, template
- **FITTY_NUTRI.MD**: Modulo nutrizione - entity, controller, form, template

---

## Visione Generale

**FITTY HUB** è una piattaforma che combina:
- 📱 **App gratuita B2C** per utenti finali
- 💼 **Piattaforma SaaS B2B** per professionisti (palestre, PT, nutrizionisti)

---

## 🚀 LA VERA FORZA DEL MODELLO

### 💎 **VANTAGGIO COMPETITIVO PRINCIPALE**

> **"Cambi palestra? Cambi PT? Cambi nutrizionista? L'app resta la stessa. I tuoi dati restano con te."**

#### Il Problema delle App Tradizionali:
```
❌ App della Palestra A
   └─ Cambi palestra → perdi tutto, app inutile

❌ App del PT Mario
   └─ Cambi PT → perdi tutto, app inutile

❌ 3 app diverse
   ├─ App palestra
   ├─ App PT
   └─ App nutrizionista
   └─ Confusione totale
```

#### La Soluzione FITTY HUB:
```
✅ UN'UNICA APP PER SEMPRE
   ├─ Gennaio 2024: Palestra FitGym Roma
   ├─ Marzo 2024: Aggiungi PT Mario Rossi
   ├─ Giugno 2024: Cambi palestra → PureGym Milano
   ├─ Agosto 2024: Aggiungi Nutrizionista Dott.ssa Bianchi
   ├─ Ottobre 2024: Lasci PT Mario, prendi PT Laura
   └─ TUTTO nello stesso posto, storico completo conservato
```

---

## Modello di Business

### 📱 **LIVELLO 1: App Gratuita per Utenti** (B2C)

**Chiunque può scaricare l'app e usarla GRATIS senza limiti:**

✅ **Allenamento autonomo**
- Creare le proprie schede di allenamento
- Biblioteca esercizi
- Timer e rest timer
- Tracciare serie, ripetizioni, peso
- Storico allenamenti
- Statistiche e progressi

✅ **Nutrizione autonoma**
- Creare piani alimentari
- Database alimenti
- Diario alimentare
- Conteggio calorie e macro
- Ricette e meal prep
- Progressi peso corporeo

✅ **Tutto GRATIS, senza limitazioni**
- Nessun abbonamento richiesto
- Nessun vincolo
- Piena funzionalità

**Quando serve un professionista?**
- L'utente **PUÒ scegliere** di essere seguito da un PT
- L'utente **PUÒ scegliere** di essere seguito da un nutrizionista
- L'utente **PUÒ scegliere** di iscriversi in palestra
- Ma **NON è obbligatorio** per usare l'app

---

### 💼 **LIVELLO 2: Piattaforma SaaS per Professionisti** (B2B)

**Chi PAGA?** → Solo i **professionisti** che vogliono gestire clienti

#### 1. **PALESTRA** (Organization - paga abbonamento SaaS)
Cosa ottiene:
- Dashboard gestione iscritti
- Sistema check-in/check-out presenze
- Gestione abbonamenti e pagamenti
- Gestione visite mediche
- **Può creare schede** per i propri iscritti (modulo FITTY_GYM)
- **Può offrire servizio nutrizione** (modulo FITTY_NUTRI opzionale)
- Analytics e statistiche

I suoi clienti iscritti:
- Usano l'app gratuitamente
- Vedono le schede create dalla palestra (se presenti)
- Possono comunque creare schede proprie
- Possono essere seguiti anche da PT esterni

#### 2. **PERSONAL TRAINER** (Organization - paga abbonamento SaaS)
Cosa ottiene:
- Dashboard gestione clienti
- Sistema inviti clienti
- Creazione schede personalizzate (modulo FITTY_GYM)
- Monitoraggio progressi clienti
- Chat/messaggistica con clienti

I suoi clienti:
- Usano l'app gratuitamente
- Vedono le schede create dal PT
- Possono essere iscritti a palestre (indipendente)
- Possono avere anche altri PT o nutrizionisti

#### 3. **NUTRIZIONISTA** (Organization - paga abbonamento SaaS)
Cosa ottiene:
- Dashboard gestione clienti
- Sistema inviti clienti
- Creazione piani alimentari (modulo FITTY_NUTRI)
- Monitoraggio diario alimentare clienti
- Chat/messaggistica con clienti

I suoi clienti:
- Usano l'app gratuitamente
- Vedono i piani alimentari creati dal nutrizionista
- Possono essere seguiti anche da PT (indipendente)
- Possono creare propri piani se vogliono

#### 4. **TEAM** (Organization - paga abbonamento bundle)
- Team di PT + Nutrizionisti
- Moduli FITTY_GYM + FITTY_NUTRI attivi
- Clienti condivisi
- Servizio completo fitness + nutrizione

---

## Scenari Pratici

### Scenario 1: Utente 100% autonomo (GRATIS)
```
Cliente: "Marco"
├─ App installata
├─ Crea le sue schede da solo
├─ Crea il suo piano alimentare
├─ Traccia tutto autonomamente
└─ NON paga nulla, NON è seguito da nessuno
```

### Scenario 2: Utente autonomo + decide di provare un PT
```
Cliente: "Laura"
├─ App installata (usata gratis per 6 mesi)
├─ Le sue schede fai-da-te (create da lei)
├─ Decide di farsi seguire da PT "Mario Rossi"
│  └─ PT crea scheda professionale "Ipertrofia Avanzata"
└─ App mostra:
   ├─ Schede "fai-da-te" di Laura (può tenerle)
   └─ Scheda "professionale" del PT Mario (nuova)
```

### Scenario 3: Utente si iscrive in palestra
```
Cliente: "Giovanni"
├─ App installata (gratis)
├─ Si iscrive a palestra "FitGym Roma"
│  ├─ Abbonamento: €50/mese (paga alla palestra, NON all'app)
│  ├─ Palestra gli crea scheda "Base Sala Pesi"
│  └─ Check-in presenze quando va in palestra
└─ App mostra:
   ├─ Scheda "Base" (della palestra)
   └─ Può comunque creare schede sue
```

### Scenario 4: Iscritto palestra + PT esterno + sue schede
```
Cliente: "Sofia"
├─ App installata (gratis)
├─ Palestra "FitGym Roma"
│  └─ Scheda: "Tonificazione" (creata dalla palestra)
├─ PT esterno "Mario Rossi"
│  └─ Scheda: "HIIT Avanzato" (creata dal PT)
├─ Sue schede personali
│  └─ Scheda: "Yoga Mattutino" (creata da lei)
└─ App mostra tutte e 3 le schede
```

### Scenario 5: Team completo (PT + Nutrizionista)
```
Cliente: "Andrea"
├─ App installata (gratis)
├─ Seguito da: Team "Body Studio"
│  ├─ PT "Luca"
│  │  └─ Scheda: "Forza 4x/week"
│  └─ Nutrizionista "Dott.ssa Bianchi"
│     └─ Piano: "Dieta 2000kcal"
└─ App mostra:
   ├─ Scheda allenamento professionale
   ├─ Piano nutrizionale professionale
   └─ Può comunque tracciare pasti extra
```

### Scenario 6: Solo nutrizionista
```
Cliente: "Francesca"
├─ App installata (gratis)
├─ Crea schede allenamento da sola (gratis)
├─ Seguita da: Nutrizionista "Dott.ssa Verdi"
│  └─ Piano: "Ricomposizione Corporea"
└─ App mostra:
   ├─ Schede allenamento sue
   └─ Piano nutrizionale professionale
```

---

## Architettura Database

### Approccio: **Relazioni Normali (semplice)**

**Semplice e chiaro:**
- Ogni entity ha relazioni normali
- Nessun filtro globale complicato
- Query dirette con Doctrine

### 🎯 Principi Fondamentali

1. **User (Cliente)**: Può esistere senza organizzazione (utente free)
2. **Organization**: Palestra/PT/Nutrizionista che paga l'abbonamento SaaS
3. **Content (Schede/Piani)**: Può essere creato da user (gratis) o organization (professionale)

### Logica Chiave

```
Contenuto creato da utente:
├─ organization_id = NULL
├─ createdBy_id = user_id
└─ planType = "user_created"

Contenuto creato da professionista:
├─ organization_id = organization che l'ha creato
├─ createdBy_id = staff_user_id
└─ planType = "professional"
```

### Query Esempio

```php
// PT Mario vuole vedere i suoi clienti
$myClients = $clientOrganizationRelationRepository->findBy([
    'organization' => $currentOrganization,
    'status' => 'active'
]);

// Super Admin vuole vedere tutte le palestre
$allGyms = $organizationRepository->findBy([
    'type' => 'gym',
    'subscriptionStatus' => 'active'
]);

// Cliente Marco vuole vedere le sue schede
$myPlans = $workoutPlanRepository->findBy(['client' => $user]);
// Vede schede da TUTTE le organization + le sue personali
```

---

## Entity Core Condivise

### User (Cliente finale)
```php
- id (uuid)
- email (unique)
- password (hashed)
- firstName
- lastName
- dateOfBirth
- gender (male, female, other)
- phoneNumber
- address, city, postalCode
- profileImage (path)
- createdAt
- updatedAt

Relazioni:
- organizationRelations (OneToMany → ClientOrganizationRelation)
- workoutPlans (OneToMany → WorkoutPlan)
- nutritionPlans (OneToMany → NutritionPlan)
```

**Nota:** Tutti gli utenti sono sempre FREE. Non pagano mai.

---

### Organization (Organizzazione B2B)
```php
- id (uuid)
- name (es. "FitGym Roma", "PT Mario Rossi")
- type (gym, personal_trainer, nutritionist, team)
- slug (univoco, per URL/sottodominio)
- description
- logo (path)
- address, city, postalCode
- phoneNumber
- email (billing)
- website
- vatNumber

// Moduli attivi
- modules (json: ['gym', 'nutri'])

// Abbonamento SaaS
- subscriptionPlan (starter, professional, enterprise)
- subscriptionStatus (trial, active, suspended, cancelled)
- subscriptionStartDate
- subscriptionEndDate
- maxClients (limite per piano)
- currentClientsCount (counter)

// Pagamento
- billingEmail
- paymentMethod
- lastPaymentDate
- nextPaymentDate

// Settings
- settings (json)
- isActive
- createdAt
- updatedAt

Relazioni:
- staff (OneToMany → OrganizationUser)
- clientRelations (OneToMany → ClientOrganizationRelation)
- workoutPlans (OneToMany → WorkoutPlan)
- nutritionPlans (OneToMany → NutritionPlan)
```

**Chi è un Organization:**
- Una palestra che paga €49-199/mese
- Un PT che paga €29-149/mese
- Un nutrizionista che paga €29-149/mese
- Un team PT+Nutri che paga bundle

---

### OrganizationUser (Staff del Organization)
```php
- id (uuid)
- organization_id (ManyToOne → Organization)
- user_id (ManyToOne → User)
- role (owner, admin, trainer, nutritionist, staff)
- specialization
- certifications (json array)
- biography (text)
- hourlyRate (per consulenze)
- isActive
- hiredAt
- terminatedAt
- createdAt
- updatedAt
```

**Chi è un OrganizationUser:**
- Il proprietario della palestra (owner)
- Un admin della palestra
- Un PT che lavora per la palestra
- Un nutrizionista del team

**Nota:** Un PT freelance è un Organization (type=personal_trainer), non un OrganizationUser.

---

### ClientOrganizationRelation (Collegamento Cliente-Professionista)
```php
- id (uuid)
- organization_id (ManyToOne → Organization)
- client_id (ManyToOne → User)
- relationType (gym_member, pt_client, nutritionist_client)
- status (pending, active, suspended, terminated)
- assignedStaff_id (ManyToOne → OrganizationUser, nullable)
- startDate
- endDate (nullable)
- notes (text)
- createdAt
- updatedAt
```

**Esempi:**
```
Cliente "Marco" + Palestra "FitGym":
- relationType: gym_member
- status: active
- GymMembership collegato (abbonamento, check-in, etc.)

Cliente "Laura" + PT "Mario Rossi":
- relationType: pt_client
- status: active
- PT può crearle schede

Cliente "Sofia" + Nutrizionista "Dott.ssa Bianchi":
- relationType: nutritionist_client
- status: active
- Nutrizionista può crearle piani
```

---

## Moduli

La piattaforma è divisa in moduli acquistabili separatamente:

### Modulo FITTY_GYM (dettagli in FITTY_GYM.MD)
- Schede allenamento
- Esercizi
- Tracciamento allenamenti
- Gestione palestre (abbonamenti, check-in, visite mediche)

### Modulo FITTY_NUTRI (dettagli in FITTY_NUTRI.MD)
- Piani nutrizionali
- Pasti e ricette
- Diario alimentare
- Database alimenti

---

## Monetizzazione

### Per Utenti (B2C)
**GRATIS** ✅ sempre

Possibili upgrade futuri (opzionali):
- Cloud backup avanzato
- Video tutorial premium
- Analisi AI personalizzate

### Per Professionisti (B2B)

**Palestre:**
| Piano | Prezzo | Clienti | Features |
|-------|--------|---------|----------|
| Starter | €49/mese | 50 | Modulo GYM base |
| Professional | €99/mese | 200 | GYM + NUTRI |
| Enterprise | €199/mese | Illimitati | Tutto + API |

**PT/Nutrizionisti:**
| Piano | Prezzo | Clienti | Features |
|-------|--------|---------|----------|
| Solo | €29/mese | 20 | 1 professionista |
| Team | €79/mese | 100 | 3-5 professionisti |
| Studio | €149/mese | Illimitati | 10+ professionisti |

---

## Vantaggi Competitivi

### Per Utenti Finali

✅ **Proprietà dei dati**: I tuoi dati sono TUOI, non della palestra
✅ **Continuità**: Cambi palestra ma mantieni tutto lo storico
✅ **Flessibilità**: Oggi palestra, domani PT, dopodomani entrambi
✅ **Zero lock-in**: Non sei vincolato a nessun professionista
✅ **Multi-servizio**: Un'app per allenamento + nutrizione + progressi
✅ **Gratuita**: Funziona anche senza professionisti
✅ **Storico completo**: Anni di allenamenti e progressi sempre disponibili

#### Esempio Reale:
```
Marco, 28 anni - Timeline 2023-2025:
├─ 2023: Si allena da solo (150 allenamenti tracciati)
├─ Gen 2024: Palestra FitGym (+ scheda palestra)
├─ Apr 2024: Trasloco → PureGym Milano
│  └─ ⭐ Storico 150 allenamenti ancora visibile
├─ Lug 2024: Aggiunge PT Mario Rossi
│  └─ ⭐ Mantiene palestra + storico completo
├─ Ott 2024: Lascia PT, continua solo palestra
│  └─ ⭐ Scheda PT consultabile in archivio
└─ 2025: Torna ad allenarsi da solo
   └─ ⭐ 2 anni di dati, può riutilizzare vecchie schede
```

### Per Professionisti

✅ **Acquisizione facilitata**: Clienti già nell'ecosistema
✅ **Nessuna barriera**: Cliente non deve scaricare "la tua app"
✅ **Professionalità**: Dashboard dedicata e strumenti pro
✅ **Retention**: Cliente vede valore anche se cambia (può tornare)
✅ **Network effect**: Più clienti = più visibilità marketplace
✅ **Focus sul servizio**: Non devi creare/gestire un'app

### Per il Business

✅ **Viralità organica**: Utenti portano altri utenti (gratis)
✅ **Conversion naturale**: Free users → clienti prof → prof paganti
✅ **Lock-in positivo**: Più dati = più valore = più retention
✅ **Network effect**: Più prof → più clienti → più prof
✅ **Scalabilità**: Un'app, migliaia di tenant
✅ **Revenue ricorrente**: B2B subscriptions prevedibili
✅ **Low churn**: Professionisti non migrano (clienti nell'ecosistema)

---

## 🔥 Use Cases Killer

### 1. "Il Nomade Digitale"
```
Freelancer che viaggia:
├─ Gen: Palestra Milano
├─ Mar: Palestra Barcellona
├─ Giu: Palestra Bali
├─ Set: Torna Milano
└─ ✅ Stessa app, stesso storico
```

### 2. "Lo Studente"
```
├─ Estate: Palestra città natale
├─ Anno: Palestra città università
├─ Esami: PT online
└─ ✅ Non perde mai il filo
```

### 3. "Il Professionista Busy"
```
├─ Palestra vicino ufficio
├─ PT online (viaggi lavoro)
├─ Nutrizionista dieta
├─ Cambia città
└─ ✅ Tutto lo segue
```

### 4. "Il Fedele Convertito"
```
├─ Palestra storica chiude (COVID)
├─ Con app tradizionale: perde TUTTO
├─ Con FITTY HUB: 3 anni di progressi salvi
└─ ✅ Nuova palestra, continua seamless
```

---

## 💰 Revenue Model

### Effetto Network (Viral Loop):
```
1. Utente scarica app gratis → usa per mesi
2. Utente si fidelizza (dati accumulati = lock-in positivo)
3. Utente cerca palestra → cerca su FITTY HUB marketplace
4. Palestra si iscrive a FITTY HUB per essere trovata
5. Palestra invita i suoi clienti → più utenti gratis
6. Ciclo si ripete
```

### KPI Target:
```
Utenti:
- Retention 12 mesi: >70%
- DAU/MAU: >40%
- Lifetime: infinito

Professionisti (Organization):
- Churn annuale: <5%
- CAC: basso
- LTV: €1000-5000 (24-36 mesi)
```

---

## Roadmap

### Fase 1: MVP App Gratuita (3-4 mesi)
1. Autenticazione utenti
2. Modulo GYM base user (schede fai-da-te)
3. Modulo NUTRI base user (piani fai-da-te)
4. PWA mobile responsive

### Fase 2: Piattaforma B2B PT (2-3 mesi)
1. Organization management
2. Dashboard PT
3. Sistema inviti clienti
4. Schede professionali

### Fase 3: Piattaforma B2B Palestre (2-3 mesi)
1. Dashboard palestre
2. Gestione abbonamenti
3. Check-in/check-out
4. Visite mediche

### Fase 4: Piattaforma B2B Nutrizionisti (2 mesi)
1. Dashboard nutrizionisti
2. Piani alimentari professionali
3. Monitoraggio diario clienti

### Fase 5: Growth & Scale (ongoing)
1. Marketplace professionisti
2. Pagamenti integrati (Stripe)
3. Analytics avanzate
4. App mobile nativa
5. AI coaching

---

## Tecnologie

**Backend:**
- Symfony 7.3 (PHP 8.2+)
- Doctrine ORM (relazioni normali con filter)
- API Platform (REST API)
- Symfony Messenger (queue)

**Frontend:**
- Twig + Stimulus (dashboard web)
- PWA (Vue.js/React) per app mobile
- TailwindCSS

**Database:**
- PostgreSQL (organization_id per isolamento)
- Redis (cache, queue, sessions)

**Storage:**
- S3 / MinIO (immagini, file PDF visite mediche)

**Infrastruttura:**
- Docker / Docker Compose
- CI/CD (GitHub Actions)
- Hosting: AWS / DigitalOcean

---

## Security & Privacy

- **Isolamento dati**: Query filtrate per organization con Doctrine
- **Voters**: Controllo accessi granulare (Workout PlanVoter, ClientAccessVoter, etc.)
- **GDPR compliance**: Export dati utente, right to be forgotten
- **Encryption**: Dati sensibili (certificati medici) crittografati
- **Rate limiting**: Protezione API
- **CSRF protection**: Attivo su tutti i form

---

## Conclusione

FITTY HUB risolve il problema del **lock-in** delle app fitness tradizionali, dando agli utenti:
- **Libertà** di cambiare professionisti senza perdere dati
- **Continuità** multi-anno del proprio percorso fitness
- **Flessibilità** di combinare servizi multipli

Mentre i professionisti ottengono:
- **Strumento professionale** senza sviluppare un'app
- **Acquisizione clienti** facilitata (già nell'ecosistema)
- **Focus sul servizio** invece che sulla tecnologia

È un **win-win-win** per utenti, professionisti e business.

---

**Prossimi passi:** Vedere FITTY_GYM.MD e FITTY_NUTRI.MD per dettagli implementativi.