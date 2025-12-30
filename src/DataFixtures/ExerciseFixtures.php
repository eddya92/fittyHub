<?php

namespace App\DataFixtures;

use App\Domain\Workout\Entity\Exercise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ExerciseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $exercises = $this->getExercisesData();

        foreach ($exercises as $data) {
            $exercise = new Exercise();
            $exercise->setName($data['name']);
            $exercise->setDescription($data['description']);
            $exercise->setCategory($data['category']);
            $exercise->setMuscleGroups($data['muscleGroups']);
            $exercise->setDifficulty($data['difficulty']);
            $exercise->setEquipment($data['equipment']);
            $exercise->setInstructions($data['instructions'] ?? null);

            $manager->persist($exercise);
        }

        $manager->flush();
    }

    private function getExercisesData(): array
    {
        return [
            // CHEST EXERCISES
            [
                'name' => 'Panca Piana Bilanciere',
                'description' => 'Esercizio fondamentale per il petto con bilanciere',
                'category' => 'chest',
                'muscleGroups' => ['pectoralis_major', 'anterior_deltoid', 'triceps'],
                'difficulty' => 'intermediate',
                'equipment' => 'barbell',
                'instructions' => 'Sdraiati sulla panca, impugna il bilanciere con presa poco più larga delle spalle. Abbassa il bilanciere al petto e spingi verso l\'alto.'
            ],
            [
                'name' => 'Panca Inclinata Manubri',
                'description' => 'Esercizio per la parte alta del petto',
                'category' => 'chest',
                'muscleGroups' => ['pectoralis_major_clavicular', 'anterior_deltoid', 'triceps'],
                'difficulty' => 'intermediate',
                'equipment' => 'dumbbell',
                'instructions' => 'Su panca inclinata a 30-45°, spingi i manubri verso l\'alto sopra il petto.'
            ],
            [
                'name' => 'Flessioni',
                'description' => 'Esercizio a corpo libero per petto e tricipiti',
                'category' => 'chest',
                'muscleGroups' => ['pectoralis_major', 'triceps', 'anterior_deltoid'],
                'difficulty' => 'beginner',
                'equipment' => 'bodyweight',
                'instructions' => 'In posizione di plank, abbassa il corpo verso il pavimento e spingi indietro.'
            ],

            // BACK EXERCISES
            [
                'name' => 'Stacchi da Terra',
                'description' => 'Esercizio composto fondamentale per schiena e gambe',
                'category' => 'back',
                'muscleGroups' => ['erector_spinae', 'latissimus_dorsi', 'glutes', 'hamstrings'],
                'difficulty' => 'advanced',
                'equipment' => 'barbell',
                'instructions' => 'Con schiena dritta, solleva il bilanciere da terra estendendo le gambe e il busto.'
            ],
            [
                'name' => 'Trazioni alla Sbarra',
                'description' => 'Esercizio per la schiena a corpo libero',
                'category' => 'back',
                'muscleGroups' => ['latissimus_dorsi', 'biceps', 'middle_trapezius'],
                'difficulty' => 'intermediate',
                'equipment' => 'pullup_bar',
                'instructions' => 'Appesi alla sbarra, tira il corpo verso l\'alto fino a portare il mento sopra la sbarra.'
            ],
            [
                'name' => 'Rematore Bilanciere',
                'description' => 'Esercizio per lo spessore della schiena',
                'category' => 'back',
                'muscleGroups' => ['latissimus_dorsi', 'rhomboids', 'middle_trapezius', 'biceps'],
                'difficulty' => 'intermediate',
                'equipment' => 'barbell',
                'instructions' => 'Piegato in avanti, tira il bilanciere verso il basso addome mantenendo la schiena dritta.'
            ],

            // LEGS EXERCISES
            [
                'name' => 'Squat Bilanciere',
                'description' => 'Re degli esercizi per le gambe',
                'category' => 'legs',
                'muscleGroups' => ['quadriceps', 'glutes', 'hamstrings', 'erector_spinae'],
                'difficulty' => 'intermediate',
                'equipment' => 'barbell',
                'instructions' => 'Con il bilanciere sulle spalle, scendi piegando le ginocchia fino a 90° e risali.'
            ],
            [
                'name' => 'Affondi',
                'description' => 'Esercizio unilaterale per gambe e glutei',
                'category' => 'legs',
                'muscleGroups' => ['quadriceps', 'glutes', 'hamstrings'],
                'difficulty' => 'beginner',
                'equipment' => 'dumbbell',
                'instructions' => 'Fai un passo avanti e piega entrambe le ginocchia a 90°, poi risali.'
            ],
            [
                'name' => 'Leg Press',
                'description' => 'Esercizio per gambe alla macchina',
                'category' => 'legs',
                'muscleGroups' => ['quadriceps', 'glutes', 'hamstrings'],
                'difficulty' => 'beginner',
                'equipment' => 'machine',
                'instructions' => 'Seduto alla macchina, spingi la piattaforma con i piedi estendendo le gambe.'
            ],

            // SHOULDERS EXERCISES
            [
                'name' => 'Military Press',
                'description' => 'Esercizio fondamentale per le spalle',
                'category' => 'shoulders',
                'muscleGroups' => ['anterior_deltoid', 'middle_deltoid', 'triceps'],
                'difficulty' => 'intermediate',
                'equipment' => 'barbell',
                'instructions' => 'In piedi, spingi il bilanciere sopra la testa partendo dalle spalle.'
            ],
            [
                'name' => 'Alzate Laterali',
                'description' => 'Esercizio di isolamento per deltoide laterale',
                'category' => 'shoulders',
                'muscleGroups' => ['middle_deltoid'],
                'difficulty' => 'beginner',
                'equipment' => 'dumbbell',
                'instructions' => 'Con manubri ai lati, solleva le braccia lateralmente fino all\'altezza delle spalle.'
            ],

            // ARMS EXERCISES
            [
                'name' => 'Curl Bilanciere',
                'description' => 'Esercizio base per i bicipiti',
                'category' => 'arms',
                'muscleGroups' => ['biceps_brachii'],
                'difficulty' => 'beginner',
                'equipment' => 'barbell',
                'instructions' => 'Con gomiti fermi, fletti gli avambracci portando il bilanciere verso le spalle.'
            ],
            [
                'name' => 'French Press',
                'description' => 'Esercizio per i tricipiti',
                'category' => 'arms',
                'muscleGroups' => ['triceps_brachii'],
                'difficulty' => 'intermediate',
                'equipment' => 'barbell',
                'instructions' => 'Sdraiato o seduto, estendi le braccia sopra la testa con il bilanciere.'
            ],
            [
                'name' => 'Dips',
                'description' => 'Esercizio a corpo libero per tricipiti e petto',
                'category' => 'arms',
                'muscleGroups' => ['triceps_brachii', 'pectoralis_major', 'anterior_deltoid'],
                'difficulty' => 'intermediate',
                'equipment' => 'parallels',
                'instructions' => 'Alle parallele, scendi piegando i gomiti e risali estendendo le braccia.'
            ],

            // ABS EXERCISES
            [
                'name' => 'Crunch',
                'description' => 'Esercizio base per gli addominali',
                'category' => 'abs',
                'muscleGroups' => ['rectus_abdominis'],
                'difficulty' => 'beginner',
                'equipment' => 'bodyweight',
                'instructions' => 'Sdraiato sulla schiena, solleva le spalle da terra contraendo gli addominali.'
            ],
            [
                'name' => 'Plank',
                'description' => 'Esercizio isometrico per il core',
                'category' => 'abs',
                'muscleGroups' => ['rectus_abdominis', 'transversus_abdominis', 'erector_spinae'],
                'difficulty' => 'beginner',
                'equipment' => 'bodyweight',
                'instructions' => 'In posizione di push-up sui gomiti, mantieni il corpo in linea retta.'
            ],

            // CARDIO EXERCISES
            [
                'name' => 'Corsa Tapis Roulant',
                'description' => 'Cardio base sul tapis roulant',
                'category' => 'cardio',
                'muscleGroups' => ['cardiovascular_system', 'quadriceps', 'hamstrings', 'calves'],
                'difficulty' => 'beginner',
                'equipment' => 'treadmill',
                'instructions' => 'Corri o cammina sul tapis roulant a velocità e inclinazione desiderate.'
            ],
            [
                'name' => 'Cyclette',
                'description' => 'Cardio a basso impatto',
                'category' => 'cardio',
                'muscleGroups' => ['cardiovascular_system', 'quadriceps', 'hamstrings'],
                'difficulty' => 'beginner',
                'equipment' => 'bike',
                'instructions' => 'Pedala mantenendo una resistenza e velocità costanti.'
            ],
        ];
    }
}
