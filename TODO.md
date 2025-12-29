# FITTY HUB - TODO List

## ✅ Completati

### Backend API
- [x] Setup Symfony 7.3.4 + API Platform 4.2
- [x] Implementazione DDD con 7 domini (User, Gym, PersonalTrainer, Membership, Workout, Medical, Invitation)
- [x] JWT Authentication con LexikJWTAuthenticationBundle
- [x] Sistema email con template Twig
- [x] Workflow inviti (Gym→PT, PT→Client)
- [x] Symfony Voters per autorizzazione
- [x] UseCase pattern per business logic
- [x] File upload service per certificati medici
- [x] Soft delete trait
- [x] Rate limiting
- [x] Custom validators
- [x] API filters
- [x] Documentazione API disponibile su `/api/docs`

### Backoffice Web (Twig + Stimulus + TailwindCSS)
- [x] Setup TailwindCSS via CDN
- [x] Sistema login/logout con remember me
- [x] Registrazione utente base
- [x] Registrazione palestra (2-step: admin + gym info)
- [x] Layout base responsive con sidebar

### Area Admin Palestra
- [x] Dashboard con statistiche (iscritti attivi, PT, iscrizioni totali)
- [x] Gestione iscritti (lista, dettaglio, filtri, ricerca)
- [x] Modifica dati utente e iscrizione (date, stato, note, dati personali)
- [x] Integrazione certificato medico nel dettaglio iscritto
- [x] Upload certificato medico per utente (da admin)
- [x] Gestione Personal Trainer (lista, dettaglio, filtri)
- [x] Gestione inviti PT (lista, crea, reinvia, cancella, filtri)
- [x] Sistema flash messages per feedback utente

### Area Personal Trainer
- [x] Dashboard PT con statistiche (clienti totali/attivi, piani allenamento)
- [x] Lista clienti assegnati dalla palestra
- [x] Dettaglio cliente con informazioni relazione
- [x] Filtri e ricerca clienti

## 🔄 In Corso / Da Fare

### Area Admin Palestra
- [x] **Gestione Corsi**
  - [x] CRUD corsi (nome, descrizione, categoria personalizzabile, capacità max, istruttore)
  - [x] Calendario settimanale corsi con orari e giorni
  - [x] Iscrizione/cancellazione utenti ai corsi
  - [x] Gestione orari settimanali (aggiungi/elimina)
  - [x] Controllo posti disponibili automatico
  - [x] Categorie corso personalizzabili con colore
  - [x] CRUD categorie personalizzate (nome + colore hex)
  - [ ] Gestione presenze/check-in corsi
  - [ ] Report partecipazione corsi
- [x] **Sistema Check-in Tornello**
  - [x] Impostazione per abilitare/disabilitare check-in tornello
  - [x] Validazione automatica (abbonamento + certificato medico)
  - [x] Blocco ingresso se abbonamento scaduto
  - [x] Blocco ingresso se certificato medico scaduto
  - [x] Dashboard con statistiche check-in giornalieri
  - [x] Interfaccia scansione (email o ID utente)
  - [x] Storico presenze con dettagli utente
  - [x] Test unitari per business logic e entità

### Area Personal Trainer
- [ ] **Gestione Piani Allenamento**
  - [ ] Form creazione piano allenamento completo
  - [ ] Dettaglio piano con esercizi, serie, ripetizioni
  - [ ] Assegnazione esercizi da database
  - [ ] Modifica/aggiornamento piani esistenti
  - [ ] Storico modifiche piani

### Mobile App (Flutter)
- [ ] Setup progetto Flutter
- [ ] Implementare autenticazione JWT
- [ ] UI/UX Design system
- [ ] Area utente cliente
- [ ] Visualizzazione piani allenamento
- [ ] Prenotazione corsi
- [ ] Gestione certificato medico
- [ ] Notifiche push

## 📋 Backlog / Future Features

### Backend
- [ ] Sistema di pagamenti (Stripe/PayPal)
- [ ] Gestione abbonamenti ricorrenti
- [ ] Report e analytics avanzati
- [ ] Export dati (PDF, Excel)
- [ ] Sistema notifiche real-time
- [ ] Integrazione calendario esterno (Google Calendar, iCal)

### Backoffice Web
- [ ] Grafici e statistiche avanzate
- [ ] Gestione fatturazione
- [ ] Report PDF personalizzati
- [ ] Importazione dati bulk (CSV)
- [ ] Sistema messaggistica interna

### Mobile App
- [ ] Tracking allenamenti
- [ ] Statistiche progressi personali
- [ ] Social features (condivisione risultati)
- [ ] Integrazione wearable (Apple Watch, Fitbit)
- [ ] Modalità offline

## 🐛 Bug da Fixare

_(Nessun bug noto al momento)_

## 📝 Note Tecniche

### Stack Tecnologico
- **Backend**: Symfony 7.3.4, API Platform 4.2, PHP 8.2+, MySQL 8.0
- **Backoffice Web**: Twig, Stimulus, TailwindCSS 4.1.18 (CDN)
- **Mobile**: Flutter (in planning)
- **Auth**: JWT (LexikJWTAuthenticationBundle)
- **Email**: Symfony Mailer + Mailhog (dev)

### Architettura
- DDD (Domain-Driven Design) con 7 domini separati
- Repository pattern per data access
- UseCase pattern per business logic
- Voter pattern per authorization
- State Processor pattern (API Platform)

### Security Roles
```
ROLE_USER → Utente base (cliente palestra)
ROLE_PT → Personal Trainer (eredita ROLE_USER)
ROLE_ADMIN → Admin palestra (eredita ROLE_USER)
ROLE_SUPER_ADMIN → Super admin (eredita tutti)
```

### Business Model
- **B2B**: Palestre pagano per usare la piattaforma
- **B2C**: Utenti usano app mobile gratuitamente
- **PT Model**: Solo PT dipendenti della palestra (non freelance indipendenti)

---

**Ultimo aggiornamento**: 2025-12-29
