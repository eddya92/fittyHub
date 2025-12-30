<?php

namespace App\Tests\Domain\Workout\Repository;

use App\Domain\Workout\Entity\Exercise;
use App\Domain\Workout\Repository\ExerciseRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ExerciseRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ExerciseRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        // Reset database before each test
        \App\Tests\DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(Exercise::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testFindAllActiveReturnsOnlyActiveExercises(): void
    {
        // Create active exercise
        $activeExercise = new Exercise();
        $activeExercise->setName('Active Exercise');
        $activeExercise->setCategory('chest');
        $activeExercise->setMuscleGroups(['pectoralis_major']);
        $activeExercise->setDifficulty('beginner');
        $activeExercise->setIsActive(true);
        $this->entityManager->persist($activeExercise);

        // Create inactive exercise
        $inactiveExercise = new Exercise();
        $inactiveExercise->setName('Inactive Exercise');
        $inactiveExercise->setCategory('back');
        $inactiveExercise->setMuscleGroups(['latissimus_dorsi']);
        $inactiveExercise->setDifficulty('beginner');
        $inactiveExercise->setIsActive(false);
        $this->entityManager->persist($inactiveExercise);

        $this->entityManager->flush();

        $results = $this->repository->findAllActive();

        $this->assertCount(1, $results);
        $this->assertEquals('Active Exercise', $results[0]->getName());
    }

    public function testFindAllActiveOrdersByCategoryThenName(): void
    {
        $exercise1 = new Exercise();
        $exercise1->setName('Bench Press');
        $exercise1->setCategory('chest');
        $exercise1->setMuscleGroups(['pectoralis_major']);
        $exercise1->setDifficulty('intermediate');
        $this->entityManager->persist($exercise1);

        $exercise2 = new Exercise();
        $exercise2->setName('Pull Up');
        $exercise2->setCategory('back');
        $exercise2->setMuscleGroups(['latissimus_dorsi']);
        $exercise2->setDifficulty('intermediate');
        $this->entityManager->persist($exercise2);

        $exercise3 = new Exercise();
        $exercise3->setName('Chest Fly');
        $exercise3->setCategory('chest');
        $exercise3->setMuscleGroups(['pectoralis_major']);
        $exercise3->setDifficulty('beginner');
        $this->entityManager->persist($exercise3);

        $this->entityManager->flush();

        $results = $this->repository->findAllActive();

        $this->assertCount(3, $results);
        // Should be ordered: back (Pull Up), chest (Bench Press, Chest Fly)
        $this->assertEquals('back', $results[0]->getCategory());
        $this->assertEquals('chest', $results[1]->getCategory());
        $this->assertEquals('Bench Press', $results[1]->getName());
        $this->assertEquals('Chest Fly', $results[2]->getName());
    }

    public function testFindByCategoryReturnsOnlyExercisesInCategory(): void
    {
        $chestExercise = new Exercise();
        $chestExercise->setName('Bench Press');
        $chestExercise->setCategory('chest');
        $chestExercise->setMuscleGroups(['pectoralis_major']);
        $chestExercise->setDifficulty('intermediate');
        $this->entityManager->persist($chestExercise);

        $backExercise = new Exercise();
        $backExercise->setName('Deadlift');
        $backExercise->setCategory('back');
        $backExercise->setMuscleGroups(['erector_spinae']);
        $backExercise->setDifficulty('advanced');
        $this->entityManager->persist($backExercise);

        $this->entityManager->flush();

        $chestResults = $this->repository->findByCategory('chest');
        $backResults = $this->repository->findByCategory('back');

        $this->assertCount(1, $chestResults);
        $this->assertEquals('Bench Press', $chestResults[0]->getName());

        $this->assertCount(1, $backResults);
        $this->assertEquals('Deadlift', $backResults[0]->getName());
    }

    public function testSearchByNameFindsPartialMatches(): void
    {
        $exercise1 = new Exercise();
        $exercise1->setName('Bench Press');
        $exercise1->setCategory('chest');
        $exercise1->setMuscleGroups(['pectoralis_major']);
        $exercise1->setDifficulty('intermediate');
        $this->entityManager->persist($exercise1);

        $exercise2 = new Exercise();
        $exercise2->setName('Incline Bench Press');
        $exercise2->setCategory('chest');
        $exercise2->setMuscleGroups(['pectoralis_major']);
        $exercise2->setDifficulty('intermediate');
        $this->entityManager->persist($exercise2);

        $exercise3 = new Exercise();
        $exercise3->setName('Squat');
        $exercise3->setCategory('legs');
        $exercise3->setMuscleGroups(['quadriceps']);
        $exercise3->setDifficulty('intermediate');
        $this->entityManager->persist($exercise3);

        $this->entityManager->flush();

        $results = $this->repository->searchByName('Bench');

        $this->assertCount(2, $results);
        $names = array_map(fn($e) => $e->getName(), $results);
        $this->assertContains('Bench Press', $names);
        $this->assertContains('Incline Bench Press', $names);
    }

    public function testSearchByNameIsCaseInsensitive(): void
    {
        $exercise = new Exercise();
        $exercise->setName('Push Up');
        $exercise->setCategory('chest');
        $exercise->setMuscleGroups(['pectoralis_major']);
        $exercise->setDifficulty('beginner');
        $this->entityManager->persist($exercise);
        $this->entityManager->flush();

        $resultsLower = $this->repository->searchByName('push');
        $resultsUpper = $this->repository->searchByName('PUSH');
        $resultsMixed = $this->repository->searchByName('PuSh');

        $this->assertCount(1, $resultsLower);
        $this->assertCount(1, $resultsUpper);
        $this->assertCount(1, $resultsMixed);
    }

    public function testFindByMuscleGroupFindsExercisesWithSpecificMuscle(): void
    {
        $exercise1 = new Exercise();
        $exercise1->setName('Bench Press');
        $exercise1->setCategory('chest');
        $exercise1->setMuscleGroups(['pectoralis_major', 'triceps']);
        $exercise1->setDifficulty('intermediate');
        $this->entityManager->persist($exercise1);

        $exercise2 = new Exercise();
        $exercise2->setName('Tricep Dips');
        $exercise2->setCategory('arms');
        $exercise2->setMuscleGroups(['triceps', 'anterior_deltoid']);
        $exercise2->setDifficulty('intermediate');
        $this->entityManager->persist($exercise2);

        $exercise3 = new Exercise();
        $exercise3->setName('Bicep Curl');
        $exercise3->setCategory('arms');
        $exercise3->setMuscleGroups(['biceps']);
        $exercise3->setDifficulty('beginner');
        $this->entityManager->persist($exercise3);

        $this->entityManager->flush();

        $results = $this->repository->findByMuscleGroup('triceps');

        $this->assertCount(2, $results);
        $names = array_map(fn($e) => $e->getName(), $results);
        $this->assertContains('Bench Press', $names);
        $this->assertContains('Tricep Dips', $names);
    }

    public function testGetAllCategoriesReturnsUniqueCategories(): void
    {
        $exercise1 = new Exercise();
        $exercise1->setName('Exercise 1');
        $exercise1->setCategory('chest');
        $exercise1->setMuscleGroups(['pectoralis_major']);
        $exercise1->setDifficulty('beginner');
        $this->entityManager->persist($exercise1);

        $exercise2 = new Exercise();
        $exercise2->setName('Exercise 2');
        $exercise2->setCategory('chest');
        $exercise2->setMuscleGroups(['pectoralis_major']);
        $exercise2->setDifficulty('beginner');
        $this->entityManager->persist($exercise2);

        $exercise3 = new Exercise();
        $exercise3->setName('Exercise 3');
        $exercise3->setCategory('back');
        $exercise3->setMuscleGroups(['latissimus_dorsi']);
        $exercise3->setDifficulty('beginner');
        $this->entityManager->persist($exercise3);

        $this->entityManager->flush();

        $categories = $this->repository->getAllCategories();

        $this->assertCount(2, $categories);
        $this->assertContains('chest', $categories);
        $this->assertContains('back', $categories);
    }

    public function testGetAllCategoriesOrdersAlphabetically(): void
    {
        $exercise1 = new Exercise();
        $exercise1->setName('Exercise 1');
        $exercise1->setCategory('legs');
        $exercise1->setMuscleGroups(['quadriceps']);
        $exercise1->setDifficulty('beginner');
        $this->entityManager->persist($exercise1);

        $exercise2 = new Exercise();
        $exercise2->setName('Exercise 2');
        $exercise2->setCategory('arms');
        $exercise2->setMuscleGroups(['biceps']);
        $exercise2->setDifficulty('beginner');
        $this->entityManager->persist($exercise2);

        $exercise3 = new Exercise();
        $exercise3->setName('Exercise 3');
        $exercise3->setCategory('chest');
        $exercise3->setMuscleGroups(['pectoralis_major']);
        $exercise3->setDifficulty('beginner');
        $this->entityManager->persist($exercise3);

        $this->entityManager->flush();

        $categories = $this->repository->getAllCategories();

        $this->assertEquals(['arms', 'chest', 'legs'], $categories);
    }

    public function testRepositoryReturnsEmptyArrayWhenNoResults(): void
    {
        $this->assertEmpty($this->repository->findAllActive());
        $this->assertEmpty($this->repository->findByCategory('nonexistent'));
        $this->assertEmpty($this->repository->searchByName('nonexistent'));
        $this->assertEmpty($this->repository->getAllCategories());
    }
}
