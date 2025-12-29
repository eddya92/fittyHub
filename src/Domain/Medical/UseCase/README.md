# Medical Domain - Use Cases

## Descrizione Dominio
Gestione certificati medici per l'idoneità sportiva.

## Use Cases da Implementare

### Caricamento Certificati

1. **UploadMedicalCertificate**
   - Input: userId, membershipId, certificateType, issueDate, expiryDate, doctorName, doctorNumber, file
   - Output: MedicalCertificate creato con status "pending_review"
   - Validazioni:
     - file PDF o immagine
     - expiryDate > issueDate
     - dimensione file max 5MB
   - Business Rules: salva file in storage sicuro, path crittografato

2. **UpdateMedicalCertificate**
   - Input: certificateId, dati certificato (no file)
   - Output: MedicalCertificate aggiornato
   - Autorizzazione: solo proprietario se status = "pending_review"

### Revisione Certificati (Gym Admin)

3. **ReviewMedicalCertificate**
   - Input: certificateId, status ("approved" | "rejected"), reviewerUserId, notes
   - Output: MedicalCertificate aggiornato
   - Autorizzazione: solo GYM_ADMIN della palestra del membership
   - Business Rules:
     - reviewedAt = now
     - reviewedBy = admin
     - se approved: può attivare membership
     - se rejected: notifica utente con motivo

4. **GetPendingCertificatesForReview**
   - Input: gymId
   - Output: lista MedicalCertificate pending per quella palestra
   - Autorizzazione: solo GYM_ADMIN
   - Use Case: dashboard admin per approvazioni

### Validazione e Scadenze

5. **CheckCertificateValidity**
   - Input: certificateId
   - Output: boolean (valid/expired)
   - Business Rules: verifica expiryDate > oggi AND status = "approved"

6. **GetUserValidCertificate**
   - Input: userId
   - Output: MedicalCertificate valido più recente (se esiste)
   - Use Case: verifica all'iscrizione o check-in

7. **GetExpiringCertificates**
   - Input: gymId, daysBeforeExpiry (default: 30)
   - Output: lista certificati in scadenza
   - Use Case: reminder automatici agli utenti

### Notifiche e Reminder

8. **NotifyExpiringCertificate**
   - Input: certificateId
   - Output: notifica inviata
   - Business Rules: invia email/SMS 30, 15, 7 giorni prima scadenza

9. **SuspendMembershipForExpiredCertificate**
   - Input: membershipId
   - Output: Membership sospeso automaticamente
   - Business Rules:
     - eseguito da job automatico
     - verifica se certificato scaduto
     - sospende accesso palestra

### Storico e Download

10. **GetUserCertificateHistory**
    - Input: userId
    - Output: lista tutti i certificati (inclusi scaduti)
    - Use Case: storico certificazioni utente

11. **DownloadCertificate**
    - Input: certificateId
    - Output: file certificato
    - Autorizzazione: proprietario, admin palestra del membership, o SUPER_ADMIN
    - Business Rules: log accessi per GDPR

### Tipologie Certificati

**Certificato NON Agonistico:**
- Validità: 12 mesi
- Richiesto per: accesso palestra normale
- Rilasciato da: medico di base o medico sportivo

**Certificato Agonistico:**
- Validità: 12 mesi (minorenni), 24 mesi (over 35 alcuni sport)
- Richiesto per: attività agonistica
- Rilasciato da: solo medico sportivo
- Include: ECG, esame urine, spirometria

### Analytics

12. **GetCertificateComplianceStats**
    - Input: gymId
    - Output: statistiche compliance
    - Metriche:
      - % membri con certificato valido
      - certificati in scadenza prossimi 30gg
      - certificati pending review
      - certificati scaduti
    - Use Case: dashboard admin per monitorare compliance legale
