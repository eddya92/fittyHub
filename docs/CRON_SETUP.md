# Configurazione CRON per FittyHub

## Indice
1. [Rigenerazione Mensile Sessioni Corsi](#rigenerazione-mensile-sessioni-corsi)
2. [Promemoria Corsi (1 ora prima)](#promemoria-corsi-1-ora-prima)
3. [Scadenza Certificati Medici](#scadenza-certificati-medici)
4. [Scadenza Abbonamenti](#scadenza-abbonamenti)
5. [Configurazione Completa Raccomandata](#configurazione-completa-raccomandata)

---

## Rigenerazione Mensile Sessioni Corsi

### Comando
```bash
php bin/console app:regenerate-course-sessions --monthly
```

### Cosa fa
1. **Elimina** tutte le sessioni future programmate (status "scheduled")
2. **Rigenera** le sessioni per mese corrente + prossimo (circa 8 settimane)
3. Si basa sull'**ultima configurazione** degli orari (CourseSchedule)

### Configurazione Crontab

#### Opzione 1: Ogni 1° del mese alle 00:00
```bash
0 0 1 * * cd /path/to/fittyHub && php bin/console app:regenerate-course-sessions --monthly >> /var/log/fittyhub-cron.log 2>&1
```

#### Opzione 2: Ogni domenica alle 23:00 (settimanale)
```bash
0 23 * * 0 cd /path/to/fittyHub && php bin/console app:regenerate-course-sessions --weeks=5 >> /var/log/fittyhub-cron.log 2>&1
```

#### Opzione 3: Ogni ultimo giorno del mese alle 23:00
```bash
0 23 28-31 * * [ $(date -d tomorrow +\%d) -eq 1 ] && cd /path/to/fittyHub && php bin/console app:regenerate-course-sessions --monthly >> /var/log/fittyhub-cron.log 2>&1
```

### Installazione

1. **Apri crontab**:
   ```bash
   crontab -e
   ```

2. **Aggiungi la riga** (sostituisci `/path/to/fittyHub` con il percorso reale):
   ```
   0 0 1 * * cd /var/www/fittyhub && php bin/console app:regenerate-course-sessions --monthly
   ```

3. **Salva e esci** (ESC + :wq in vim)

4. **Verifica**:
   ```bash
   crontab -l
   ```

### Test manuale
```bash
# Test rigenerazione mensile
php bin/console app:regenerate-course-sessions --monthly

# Test rigenerazione custom (es. 10 settimane)
php bin/console app:regenerate-course-sessions --weeks=10

# Vedere le sessioni create
php bin/console app:list-course-sessions
```

### Nota importante
⚠️ **Il comando elimina SOLO sessioni con status "scheduled"**. Le sessioni completate o cancellate vengono preservate per lo storico.

### Logging
Per monitorare l'esecuzione del cron:
```bash
tail -f /var/log/fittyhub-cron.log
```

### Docker
Se usi Docker, aggiungi il cron nel container o crea uno script esterno:

```bash
# Script esterno: docker-cron.sh
#!/bin/bash
docker exec fittyhub-app php bin/console app:regenerate-course-sessions --monthly
```

Poi aggiungi al crontab:
```
0 0 1 * * /path/to/docker-cron.sh >> /var/log/fittyhub-docker-cron.log 2>&1
```

---

## Promemoria Corsi (1 ora prima)

### Comando
```bash
php bin/console app:courses:send-reminders
```

### Cosa fa
1. **Trova** sessioni che iniziano tra 55-65 minuti (finestra di 10 minuti)
2. **Invia email** a tutti gli utenti iscritti con promemoria
3. Include dettagli sessione, orario, istruttore

### Configurazione Crontab

#### Ogni ora (raccomandato)
```bash
0 * * * * cd /path/to/fittyHub && php bin/console app:courses:send-reminders >> /var/log/fittyhub-reminders.log 2>&1
```

#### Ogni 30 minuti (più preciso)
```bash
*/30 * * * * cd /path/to/fittyHub && php bin/console app:courses:send-reminders >> /var/log/fittyhub-reminders.log 2>&1
```

### Test manuale
```bash
# Invia promemoria per sessioni nelle prossime 1-2 ore
php bin/console app:courses:send-reminders
```

---

## Scadenza Certificati Medici

### Comando
```bash
php bin/console app:certificates:send-expiry-reminders
```

### Cosa fa
1. **Trova** certificati che scadono tra 30, 14 o 7 giorni
2. **Invia email** all'utente con avviso scadenza
3. Ricorda di caricare nuovo certificato

### Configurazione Crontab

#### Ogni giorno alle 09:00 (raccomandato)
```bash
0 9 * * * cd /path/to/fittyHub && php bin/console app:certificates:send-expiry-reminders >> /var/log/fittyhub-certificates.log 2>&1
```

### Test manuale
```bash
# Invia promemoria per certificati in scadenza
php bin/console app:certificates:send-expiry-reminders
```

---

## Scadenza Abbonamenti

### Comando
```bash
php bin/console app:memberships:expire
```

### Cosa fa
1. **Marca** come "expired" gli abbonamenti scaduti
2. Controlla tutti gli abbonamenti attivi
3. Cambia status se endDate < oggi

### Configurazione Crontab

#### Ogni giorno alle 00:01 (raccomandato)
```bash
1 0 * * * cd /path/to/fittyHub && php bin/console app:memberships:expire >> /var/log/fittyhub-memberships.log 2>&1
```

### Test manuale
```bash
# Marca abbonamenti scaduti
php bin/console app:memberships:expire
```

---

## Configurazione Completa Raccomandata

Aggiungi queste righe al tuo crontab (`crontab -e`):

```bash
# FittyHub - Sistema Notifiche e Manutenzione
# ==========================================

# Rigenerazione sessioni corsi (1° del mese alle 00:00)
0 0 1 * * cd /var/www/fittyhub && php bin/console app:regenerate-course-sessions --monthly >> /var/log/fittyhub-cron.log 2>&1

# Scadenza abbonamenti (ogni giorno alle 00:01)
1 0 * * * cd /var/www/fittyhub && php bin/console app:memberships:expire >> /var/log/fittyhub-memberships.log 2>&1

# Promemoria certificati medici (ogni giorno alle 09:00)
0 9 * * * cd /var/www/fittyhub && php bin/console app:certificates:send-expiry-reminders >> /var/log/fittyhub-certificates.log 2>&1

# Promemoria corsi 1h prima (ogni ora)
0 * * * * cd /var/www/fittyhub && php bin/console app:courses:send-reminders >> /var/log/fittyhub-reminders.log 2>&1
```

### Verifica configurazione
```bash
# Lista cron attivi
crontab -l

# Monitora log in tempo reale
tail -f /var/log/fittyhub-*.log

# Test manuale di tutti i command
php bin/console list | grep app:
```

### Note importanti
- ⚠️ **Sostituisci** `/var/www/fittyhub` con il percorso reale dell'applicazione
- 📧 Verifica configurazione email in `.env` (`MAILER_DSN`)
- 🐳 Per Docker, usa `docker exec` nei comandi (vedi sezione Rigenerazione Corsi)
- 📊 I log aiutano a debuggare problemi di invio email

### Monitoraggio
```bash
# Conta email inviate oggi
grep "Promemoria inviato" /var/log/fittyhub-*.log | wc -l

# Verifica errori
grep "ERROR" /var/log/fittyhub-*.log

# Ultimo run di ogni command
ls -lht /var/log/fittyhub-*.log
```
