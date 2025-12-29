# Regole Architetturali FITTY HUB

## 🎯 MANTRA: Fat Model, Skinny Controller

### Controller - SEMPRE snelli
**I Controller devono SOLO orchestrare, MAI contenere logica business o query.**

#### ✅ Controller DEVONO:
- Ricevere Request
- Chiamare Services
- Restituire Response/Redirect
- Gestire flash messages
- Dependency Injection via costruttore

#### ❌ Controller NON DEVONO MAI:
- Contenere QueryBuilder o query DQL
- Contenere logica business (validazioni, calcoli, trasformazioni)
- Istanziare entità direttamente (usare Services)
- Fare loop o elaborazioni complesse
- Accedere direttamente a repository per fare query (solo `find()`, `findAll()` semplici)

### Service Layer - Logica Business
**Tutta la logica applicativa DEVE stare nei Service.**

#### Responsabilità:
- Validazioni business
- Creazione/modifica entità
- Orchestrazione tra più repository
- Trasformazioni dati
- Logica di dominio

#### Esempio:
```php
class CourseService
{
    public function createCourse(array $data, Gym $gym): GymCourse
    {
        // Validazioni
        // Creazione entità
        // Relazioni
        // Persistenza
        return $course;
    }
}
```

### Repository - Query
**Query complesse DEVONO stare nei Repository.**

#### Responsabilità:
- Query personalizzate
- QueryBuilder complessi
- Filtri e ordinamenti
- Aggregazioni e statistiche

#### Esempio:
```php
class GymCourseRepository extends ServiceEntityRepository
{
    public function findWithFilters(?string $search, ?string $category): array
    {
        $qb = $this->createQueryBuilder('c');
        // Query complessa
        return $qb->getQuery()->getResult();
    }
}
```

## 📐 Struttura Standard

```
Controller/
├─ Riceve Request
├─ Chiama Service
└─ Restituisce Response

Service/
├─ Logica business
├─ Validazioni
├─ Chiama Repository
└─ Restituisce dati/entità

Repository/
├─ Query complesse
├─ find() custom
└─ Restituisce entità/array
```

## 🔒 Checklist Pre-Commit Controller

Prima di committare un Controller, verificare:
- [ ] Zero query inline (QueryBuilder/DQL)
- [ ] Zero logica business
- [ ] Massimo 10-15 righe per metodo
- [ ] Services iniettati via costruttore
- [ ] Solo chiamate a Service/Repository
- [ ] Gestione errori con try/catch e flash

## 📝 Esempio Completo

### ❌ SBAGLIATO
```php
public function create(Request $request) {
    $course = new GymCourse();
    $course->setName($request->get('name'));

    // MALE: Query inline
    $gym = $gymRepo->createQueryBuilder('g')
        ->join('g.admins', 'a')
        ->where('a = :user')
        ->setParameter('user', $this->getUser())
        ->getQuery()
        ->getFirstResult();

    // MALE: Logica business
    if ($course->getActiveEnrollmentsCount() >= $course->getMaxParticipants()) {
        throw new \Exception('Pieno');
    }

    $em->persist($course);
    $em->flush();
}
```

### ✅ CORRETTO
```php
public function create(Request $request): Response
{
    $gym = $this->gymUserService->getPrimaryGym($this->getUser());

    if (!$gym) {
        $this->addFlash('error', 'Nessuna palestra trovata.');
        return $this->redirectToRoute('admin_dashboard');
    }

    try {
        $this->courseService->createCourse($request->request->all(), $gym);
        $this->addFlash('success', 'Corso creato.');
        return $this->redirectToRoute('admin_courses');
    } catch (\Exception $e) {
        $this->addFlash('error', $e->getMessage());
    }

    return $this->render('admin/courses/create.html.twig', [
        'categories' => $this->courseService->getCategories(),
    ]);
}
```

## 🎓 Pattern da Seguire

1. **Un Service per dominio** (CourseService, MembershipService, etc.)
2. **Repository methods descrittivi** (`findActiveWithSchedules()` non `findBySomething()`)
3. **Eccezioni per errori business** (catch nel controller, throw nel service)
4. **Dependency Injection** sempre (mai new dentro i metodi)
5. **DTO per form complessi** (considerare FormType o DTO se form > 5 campi)

---

**Data creazione:** 2024-12-29
**Applicare a:** TUTTI i controller esistenti e futuri
**Priorità:** MASSIMA - Refactoring obbligatorio prima di nuove feature
