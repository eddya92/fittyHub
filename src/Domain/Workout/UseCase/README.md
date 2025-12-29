# Workout Domain - Use Cases

## Descrizione Dominio
Gestione schede di allenamento, esercizi e sessioni di workout.

## Use Cases da Implementare

### Gestione Schede Allenamento

1. **CreateWorkoutPlan**
   - Input: ptId, clientId, name, description, planType, goal, startDate, weeksCount, exercises[]
   - Output: WorkoutPlan creato con esercizi
   - Validazioni: PT deve avere relazione attiva con cliente
   - Business Rules: isActive = true, isTemplate = false

2. **UpdateWorkoutPlan**
   - Input: planId, dati piano
   - Output: WorkoutPlan aggiornato
   - Autorizzazione: solo PT creatore
   - Business Rules: updatedAt automatico

3. **AddExerciseToPlan**
   - Input: planId, exerciseData (name, category, muscleGroup, sets, reps, weight, etc.)
   - Output: WorkoutExercise creato
   - Business Rules: orderPosition automatico basato su dayNumber

4. **UpdateExercise**
   - Input: exerciseId, dati esercizio
   - Output: WorkoutExercise aggiornato
   - Use Case: modificare sets/reps/peso progressivamente

5. **RemoveExerciseFromPlan**
   - Input: exerciseId
   - Output: esercizio eliminato
   - Business Rules: riordina automaticamente gli altri esercizi

6. **ReorderExercises**
   - Input: planId, dayNumber, exerciseIds[] (ordinati)
   - Output: esercizi riordinati
   - Use Case: drag & drop nell'interfaccia

### Template Schede

7. **SavePlanAsTemplate**
   - Input: planId, templateName
   - Output: WorkoutPlan duplicato con isTemplate = true
   - Use Case: PT crea template riutilizzabile

8. **CreatePlanFromTemplate**
   - Input: templateId, clientId, startDate
   - Output: WorkoutPlan creato da template
   - Business Rules: duplica tutti gli esercizi, isTemplate = false

9. **GetPTTemplates**
   - Input: ptId
   - Output: lista WorkoutPlan template del PT
   - Use Case: libreria template personale

### Gestione Sessioni Allenamento

10. **LogWorkoutSession**
    - Input: planId, clientId, sessionDate, startTime, completedExercises[], bodyWeight, mood, energyLevel
    - Output: WorkoutSession creata
    - Validazioni: cliente deve avere accesso al plan
    - Business Rules:
      - calcola duration automaticamente
      - traccia esercizi completati con dati effettivi (weight, reps)

11. **CompleteWorkoutSession**
    - Input: sessionId, endTime, rating, notes
    - Output: WorkoutSession completata
    - Business Rules: calcola duration

12. **GetClientWorkoutHistory**
    - Input: clientId, dateRange
    - Output: lista WorkoutSession
    - Use Case: storico allenamenti cliente

13. **GetClientProgressStats**
    - Input: clientId, planId (opzionale)
    - Output: statistiche progressi (frequenza, volume totale, PR, etc.)
    - Analisi: confronto weight/reps nel tempo per esercizio

### Visualizzazione Schede

14. **GetActivePlansForClient**
    - Input: clientId
    - Output: lista WorkoutPlan attivi
    - Use Case: dashboard cliente, mostra schede correnti

15. **GetPlanDetails**
    - Input: planId
    - Output: WorkoutPlan completo con esercizi raggruppati per giorno
    - Autorizzazione: PT creatore o cliente assegnato

16. **GetDayWorkout**
    - Input: planId, dayNumber
    - Output: lista WorkoutExercise per quel giorno
    - Use Case: cliente apre allenamento del giorno

### Condivisione e Duplicazione

17. **DuplicatePlan**
    - Input: planId, newClientId
    - Output: WorkoutPlan duplicato
    - Use Case: PT riusa scheda per altro cliente

18. **ArchivePlan**
    - Input: planId, endDate
    - Output: WorkoutPlan con isActive = false
    - Business Rules: mantiene storico per analytics

### Analytics Avanzate

19. **GetExerciseProgressChart**
    - Input: clientId, exerciseName, dateRange
    - Output: dati per grafico (date, weight, reps, volume)
    - Use Case: vedere progressi su esercizio specifico (es. Squat)

20. **GetWorkoutFrequencyStats**
    - Input: clientId, dateRange
    - Output: statistiche frequenza (giorni/settimana, totale sessioni, streak)
    - Use Case: dashboard motivazionale
