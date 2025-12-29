# User Domain - Use Cases

## Descrizione Dominio
Gestione degli utenti del sistema (clienti, personal trainer, admin palestre).

## Use Cases da Implementare

### Registrazione e Autenticazione

1. **RegisterUser**
   - Input: email, password, firstName, lastName, dateOfBirth, gender
   - Output: User creato
   - Validazioni: email unica, password sicura
   - Business Rules: Ruolo default ROLE_USER

2. **LoginUser**
   - Input: email, password
   - Output: Token/Sessione
   - Validazioni: credenziali valide

3. **UpdateUserProfile**
   - Input: userId, dati profilo (nome, telefono, indirizzo, etc.)
   - Output: User aggiornato
   - Validazioni: solo proprietario può modificare

4. **UploadProfileImage**
   - Input: userId, file immagine
   - Output: path immagine salvata
   - Validazioni: formato immagine valido, dimensione max

### Gestione Ruoli

5. **PromoteUserToPersonalTrainer**
   - Input: userId, specialization, certifications
   - Output: PersonalTrainer profile creato
   - Business Rules: aggiunge ROLE_PT ai ruoli

6. **PromoteUserToGymAdmin**
   - Input: userId, gymId
   - Output: User con ROLE_GYM_ADMIN
   - Business Rules: solo SUPER_ADMIN può eseguire
