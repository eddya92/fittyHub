# Personal Trainer Domain - Use Cases

## Descrizione Dominio
Gestione Personal Trainer (interni ed esterni) e relazioni con i clienti.

## Use Cases da Implementare

### Gestione Profilo PT

1. **CreatePersonalTrainerProfile**
   - Input: userId, specialization, certifications, biography, hourlyRate, experience
   - Output: PersonalTrainer creato
   - Business Rules: isInternal = false di default (PT esterno)

2. **UpdatePersonalTrainerProfile**
   - Input: ptId, dati profilo
   - Output: PersonalTrainer aggiornato
   - Autorizzazione: solo il PT stesso

3. **SetAvailabilityForNewClients**
   - Input: ptId, isAvailable (boolean)
   - Output: PersonalTrainer aggiornato
   - Use Case: PT che vuole fermare nuove iscrizioni

### Gestione Clienti (PT Esterno)

4. **InviteClient**
   - Input: ptId, clientEmail, message
   - Output: PTClientInvitation creata
   - Business Rules: genera token, scadenza 7 giorni
   - Scenario: PT esterno invita nuovo cliente

5. **AcceptPTClientInvitation**
   - Input: token
   - Output: PTClientRelation creata con status "active"
   - Business Rules: se utente non esiste, deve registrarsi prima

6. **RejectPTClientInvitation**
   - Input: token
   - Output: PTClientInvitation status "rejected"

7. **SuspendClientRelation**
   - Input: relationId, reason
   - Output: PTClientRelation status "suspended"
   - Use Case: sospensione temporanea del servizio

8. **TerminateClientRelation**
   - Input: relationId, endDate, notes
   - Output: PTClientRelation status "terminated"
   - Business Rules: mantiene lo storico per analytics

### Gestione Clienti (PT Interno)

9. **AssignInternalPTToMember**
   - Input: membershipId, ptId
   - Output: Membership aggiornato con PT assegnato + PTClientRelation
   - Validazioni: PT deve essere interno della stessa palestra
   - Business Rules: crea automaticamente la relazione PT-Cliente

10. **GetPTClients**
    - Input: ptId, filters (status, orderBy)
    - Output: lista PTClientRelation
    - Use Case: dashboard PT per vedere tutti i clienti

11. **GetClientProgress**
    - Input: relationId, clientId
    - Output: statistiche progressi (workout sessions, body measurements, etc.)
    - Autorizzazione: solo il PT assegnato

### Ricerca e Discovery

12. **FindAvailablePTs**
    - Input: filters (specialization, location, hourlyRate, experience)
    - Output: lista PersonalTrainer disponibili
    - Business Rules: solo PT esterni con isAvailableForNewClients = true

13. **GetPTPublicProfile**
    - Input: ptId
    - Output: dati pubblici PT (no dati sensibili)
    - Use Case: pagina profilo pubblico PT
