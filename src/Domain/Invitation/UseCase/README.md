# Invitation Domain - Use Cases

## Descrizione Dominio
Gestione inviti tra PT-Clienti e Palestra-PT.

## Use Cases da Implementare

### Inviti PT → Cliente

1. **SendPTClientInvitation**
   - Input: ptId, clientEmail, message
   - Output: PTClientInvitation creata
   - Business Rules:
     - genera token univoco (64 caratteri)
     - expiresAt = +7 giorni
     - status = "pending"
     - verifica se email già registrata → associa clientUser
   - Trigger: invia email con link invito

2. **ResendPTClientInvitation**
   - Input: invitationId
   - Output: nuova email inviata
   - Validazioni:
     - status = "pending"
     - non scaduto
     - max 3 reinvii

3. **AcceptPTClientInvitation**
   - Input: token
   - Output: PTClientRelation creata con status "active"
   - Validazioni:
     - token valido ed esistente
     - invito non scaduto
     - status = "pending"
   - Business Rules:
     - se utente non esiste, redirect a registrazione (salvare token in sessione)
     - status invito → "accepted"
     - respondedAt = now
     - crea PTClientRelation(PT, Client, status: active, startDate: oggi)
   - Trigger: notifica PT dell'accettazione

4. **RejectPTClientInvitation**
   - Input: token, reason (opzionale)
   - Output: invito rifiutato
   - Business Rules:
     - status → "rejected"
     - respondedAt = now
   - Trigger: notifica PT del rifiuto

5. **GetPTPendingInvitations**
   - Input: ptId
   - Output: lista inviti pending non scaduti
   - Use Case: dashboard PT sezione "Inviti inviati"

6. **CancelPTClientInvitation**
   - Input: invitationId, ptId
   - Output: invito cancellato
   - Autorizzazione: solo PT mittente
   - Business Rules: status → "cancelled" (soft delete)

### Inviti Palestra → PT

7. **SendGymPTInvitation**
   - Input: gymId, ptEmail, message, invitedByUserId
   - Output: GymPTInvitation creata
   - Autorizzazione: solo GYM_ADMIN
   - Business Rules:
     - genera token univoco
     - expiresAt = +7 giorni
     - status = "pending"
     - verifica se email già registrata → associa invitedUser
   - Trigger: invia email con link invito

8. **AcceptGymPTInvitation**
   - Input: token
   - Output: PersonalTrainer associato a Gym
   - Validazioni:
     - token valido
     - invito non scaduto
     - status = "pending"
   - Business Rules:
     - se utente non esiste, redirect a registrazione come PT
     - se esiste ma non è PT, crea PersonalTrainerProfile
     - PersonalTrainer.gym = gymId
     - PersonalTrainer.isInternal = true
     - status invito → "accepted"
     - respondedAt = now
   - Trigger: notifica admin palestra

9. **RejectGymPTInvitation**
   - Input: token, reason
   - Output: invito rifiutato
   - Business Rules:
     - status → "rejected"
     - respondedAt = now
   - Trigger: notifica admin palestra

10. **GetGymPendingInvitations**
    - Input: gymId
    - Output: lista inviti pending non scaduti
    - Autorizzazione: solo GYM_ADMIN
    - Use Case: dashboard admin sezione "PT invitati"

11. **CancelGymPTInvitation**
    - Input: invitationId
    - Output: invito cancellato
    - Autorizzazione: solo GYM_ADMIN della palestra
    - Business Rules: status → "cancelled"

### Gestione Scadenze (Automatic Jobs)

12. **ExpireOldInvitations**
    - Input: -
    - Output: inviti scaduti aggiornati
    - Business Rules:
      - trova tutti inviti con expiresAt < now AND status = "pending"
      - status → "expired"
    - Trigger: cron job giornaliero

### Validazione Token

13. **ValidateInvitationToken**
    - Input: token
    - Output: Invitation (PT o Gym) se valido
    - Business Rules:
      - verifica esistenza token
      - verifica non scaduto
      - verifica status = "pending"
    - Use Case: pagina accettazione invito (mostra dati prima conferma)

### Analytics

14. **GetInvitationStats**
    - Input: ptId o gymId
    - Output: statistiche inviti
    - Metriche:
      - totale inviati
      - acceptance rate
      - rejection rate
      - expired rate
      - tempo medio risposta
    - Use Case: dashboard analytics
