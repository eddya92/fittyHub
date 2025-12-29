# Gym Domain - Use Cases

## Descrizione Dominio
Gestione palestre, presenze e amministrazione.

## Use Cases da Implementare

### Gestione Palestra

1. **CreateGym**
   - Input: name, address, city, postalCode, email, phoneNumber
   - Output: Gym creata
   - Validazioni: dati obbligatori completi
   - Business Rules: chi crea diventa admin automaticamente

2. **UpdateGymDetails**
   - Input: gymId, dati palestra
   - Output: Gym aggiornata
   - Autorizzazione: solo GYM_ADMIN della palestra

3. **SetGymOpeningHours**
   - Input: gymId, openingHours (json)
   - Output: orari salvati
   - Formato: {lunedi: "09:00-22:00", martedi: "09:00-22:00", ...}

4. **SetGymAmenities**
   - Input: gymId, amenities (array)
   - Output: servizi salvati
   - Esempi: ["Spogliatoi", "Docce", "Parcheggio", "WiFi", "Sauna"]

5. **DeactivateGym**
   - Input: gymId
   - Output: Gym disattivata
   - Business Rules: sospende tutti gli abbonamenti attivi

### Gestione Presenze

6. **RecordCheckIn**
   - Input: userId, gymId, membershipId
   - Output: GymAttendance creata con checkInTime
   - Validazioni: abbonamento attivo, visita medica valida
   - Business Rules: verifica max accessi settimanali

7. **RecordCheckOut**
   - Input: attendanceId
   - Output: GymAttendance aggiornata con checkOutTime e duration
   - Business Rules: calcola durata automaticamente

8. **GetGymAttendanceStats**
   - Input: gymId, dateRange
   - Output: statistiche presenze (totali, media giornaliera, orari di punta)
   - Use Case: dashboard admin

### Gestione PT Interni

9. **InviteInternalPT**
   - Input: gymId, ptEmail, message
   - Output: GymPTInvitation creata
   - Business Rules: genera token univoco, scadenza 7 giorni

10. **AcceptGymPTInvitation**
    - Input: token
    - Output: PersonalTrainer associato a Gym
    - Business Rules: PT diventa interno, isInternal = true

11. **RemoveInternalPT**
    - Input: gymId, ptId
    - Output: PT dissociato
    - Business Rules: PT torna esterno se non ha altre palestre
