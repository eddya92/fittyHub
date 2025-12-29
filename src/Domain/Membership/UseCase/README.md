# Membership Domain - Use Cases

## Descrizione Dominio
Gestione abbonamenti palestre e piani di sottoscrizione.

## Use Cases da Implementare

### Gestione Piani Abbonamento

1. **CreateSubscriptionPlan**
   - Input: gymId, name, description, duration, price, includePT, ptSessionsIncluded, maxAccessPerWeek, features
   - Output: SubscriptionPlan creato
   - Autorizzazione: solo GYM_ADMIN
   - Esempi: "Base 1 mese", "Premium 3 mesi + PT"

2. **UpdateSubscriptionPlan**
   - Input: planId, dati piano
   - Output: SubscriptionPlan aggiornato
   - Business Rules: non influenza abbonamenti già attivi

3. **DeactivateSubscriptionPlan**
   - Input: planId
   - Output: SubscriptionPlan isActive = false
   - Business Rules: non più acquistabile, ma abbonamenti esistenti rimangono validi

4. **GetActiveSubscriptionPlans**
   - Input: gymId
   - Output: lista piani attivi
   - Use Case: pagina iscrizione palestra

### Gestione Abbonamenti

5. **SubscribeToGym**
   - Input: userId, gymId, subscriptionPlanId, startDate, paymentMethod
   - Output: GymMembership creato con status "pending"
   - Business Rules:
     - calcola endDate in base a plan.duration
     - richiede visita medica prima di attivare
     - se plan.includePT, permette scelta PT

6. **ActivateMembership**
   - Input: membershipId
   - Output: GymMembership status "active"
   - Validazioni: visita medica approvata e valida
   - Business Rules: invia notifica benvenuto, abilita accesso fisico

7. **SuspendMembership**
   - Input: membershipId, reason, suspensionDays
   - Output: GymMembership status "suspended"
   - Business Rules: estende endDate di suspensionDays
   - Use Case: infortunio, viaggio

8. **CancelMembership**
   - Input: membershipId, reason
   - Output: GymMembership status "cancelled"
   - Business Rules: blocca accesso, gestisce rimborsi se applicabili

9. **RenewMembership**
   - Input: membershipId, newPlanId (opzionale)
   - Output: nuovo GymMembership creato
   - Business Rules:
     - se autoRenew = true, rinnovo automatico
     - verifica validità visita medica

10. **ChangeSubscriptionPlan**
    - Input: membershipId, newPlanId
    - Output: GymMembership aggiornato con nuovo piano
    - Business Rules: calcola prorata, adegua endDate

### Gestione PT nell'Abbonamento

11. **ChooseInternalPT**
    - Input: membershipId, ptId
    - Output: Membership con PT assegnato
    - Validazioni: plan.includePT = true, PT interno della palestra
    - Business Rules: crea PTClientRelation automaticamente

12. **ChangePT**
    - Input: membershipId, newPtId
    - Output: Membership aggiornato, PTClientRelation aggiornate
    - Business Rules: termina vecchia relazione, crea nuova

### Pagamenti e Rinnovi

13. **ProcessPayment**
    - Input: membershipId, amount, paymentMethod
    - Output: Membership aggiornato (lastPaymentDate, nextPaymentDate)
    - Business Rules: se autoRenew, programma prossimo pagamento

14. **GetExpiringMemberships**
    - Input: gymId, daysBeforeExpiry (default: 7)
    - Output: lista memberships in scadenza
    - Use Case: inviare reminder rinnovo

15. **GetMembershipHistory**
    - Input: userId
    - Output: storico abbonamenti tutte le palestre
    - Use Case: profilo utente

### Analytics

16. **GetGymMembershipStats**
    - Input: gymId, dateRange
    - Output: statistiche (nuovi iscritti, rinnovi, cancellazioni, revenue)
    - Autorizzazione: solo GYM_ADMIN
