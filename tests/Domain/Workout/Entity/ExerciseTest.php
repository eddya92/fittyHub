<?php

namespace App\Tests\Domain\Workout\Entity;

use App\Domain\Workout\Entity\Exercise;
use PHPUnit\Framework\TestCase;

class ExerciseTest extends TestCase
{
    private Exercise $exercise;

    protected function setUp(): void
    {
        $this->exercise = new Exercise();
    }

    public function testConstructorInitializesDefaults(): void
    {
        $exercise = new Exercise();

        $this->assertInstanceOf(\DateTimeImmutable::class, $exercise->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $exercise->getUpdatedAt());
        $this->assertTrue($exercise->isActive());
    }

    public function testCanSetAndGetName(): void
    {
        $this->exercise->setName('Panca Piana');
        $this->assertEquals('Panca Piana', $this->exercise->getName());
    }

    public function testCanSetAndGetDescription(): void
    {
        $description = 'Esercizio fondamentale per il petto';
        $this->exercise->setDescription($description);
        $this->assertEquals($description, $this->exercise->getDescription());
    }

    public function testCanSetAndGetCategory(): void
    {
        $this->exercise->setCategory('chest');
        $this->assertEquals('chest', $this->exercise->getCategory());
    }

    public function testCanSetAndGetMuscleGroups(): void
    {
        $muscles = ['pectoralis_major', 'triceps', 'anterior_deltoid'];
        $this->exercise->setMuscleGroups($muscles);
        $this->assertEquals($muscles, $this->exercise->getMuscleGroups());
    }

    public function testCanSetAndGetDifficulty(): void
    {
        $this->exercise->setDifficulty('advanced');
        $this->assertEquals('advanced', $this->exercise->getDifficulty());
    }

    public function testDefaultDifficultyIsIntermediate(): void
    {
        $exercise = new Exercise();
        $exercise->setDifficulty('intermediate');
        $this->assertEquals('intermediate', $exercise->getDifficulty());
    }

    public function testCanSetAndGetEquipment(): void
    {
        $this->exercise->setEquipment('barbell');
        $this->assertEquals('barbell', $this->exercise->getEquipment());
    }

    public function testCanSetAndGetVideoUrl(): void
    {
        $url = 'https://youtube.com/watch?v=example';
        $this->exercise->setVideoUrl($url);
        $this->assertEquals($url, $this->exercise->getVideoUrl());
    }

    public function testCanSetAndGetImageUrl(): void
    {
        $url = 'https://example.com/image.jpg';
        $this->exercise->setImageUrl($url);
        $this->assertEquals($url, $this->exercise->getImageUrl());
    }

    public function testCanSetAndGetInstructions(): void
    {
        $instructions = 'Sdraiati sulla panca e spingi il bilanciere verso l\'alto';
        $this->exercise->setInstructions($instructions);
        $this->assertEquals($instructions, $this->exercise->getInstructions());
    }

    public function testCanSetIsActive(): void
    {
        $this->exercise->setIsActive(false);
        $this->assertFalse($this->exercise->isActive());

        $this->exercise->setIsActive(true);
        $this->assertTrue($this->exercise->isActive());
    }

    public function testCompleteExerciseCreation(): void
    {
        $this->exercise->setName('Squat Bilanciere');
        $this->exercise->setDescription('Re degli esercizi per le gambe');
        $this->exercise->setCategory('legs');
        $this->exercise->setMuscleGroups(['quadriceps', 'glutes', 'hamstrings']);
        $this->exercise->setDifficulty('intermediate');
        $this->exercise->setEquipment('barbell');
        $this->exercise->setInstructions('Scendi fino a 90 gradi e risali');
        $this->exercise->setVideoUrl('https://example.com/squat');
        $this->exercise->setIsActive(true);

        $this->assertEquals('Squat Bilanciere', $this->exercise->getName());
        $this->assertEquals('legs', $this->exercise->getCategory());
        $this->assertCount(3, $this->exercise->getMuscleGroups());
        $this->assertTrue($this->exercise->isActive());
    }

    public function testTimestampsAreImmutable(): void
    {
        $createdAt = $this->exercise->getCreatedAt();
        $updatedAt = $this->exercise->getUpdatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $createdAt);
        $this->assertInstanceOf(\DateTimeImmutable::class, $updatedAt);
    }

    public function testUpdateTimestampChangesUpdatedAt(): void
    {
        $originalUpdatedAt = $this->exercise->getUpdatedAt();

        usleep(10000); // Wait 10ms

        $this->exercise->updateTimestamp();

        $this->assertGreaterThan($originalUpdatedAt, $this->exercise->getUpdatedAt());
    }

    public function testNullableFieldsCanBeNull(): void
    {
        $exercise = new Exercise();

        $this->assertNull($exercise->getDescription());
        $this->assertNull($exercise->getEquipment());
        $this->assertNull($exercise->getVideoUrl());
        $this->assertNull($exercise->getImageUrl());
        $this->assertNull($exercise->getInstructions());
    }

    public function testMuscleGroupsArrayHandling(): void
    {
        $muscles = ['chest', 'shoulders', 'triceps'];
        $this->exercise->setMuscleGroups($muscles);

        $retrievedMuscles = $this->exercise->getMuscleGroups();

        $this->assertIsArray($retrievedMuscles);
        $this->assertCount(3, $retrievedMuscles);
        $this->assertContains('chest', $retrievedMuscles);
        $this->assertContains('shoulders', $retrievedMuscles);
        $this->assertContains('triceps', $retrievedMuscles);
    }

    public function testEmptyMuscleGroupsArray(): void
    {
        $this->exercise->setMuscleGroups([]);
        $this->assertIsArray($this->exercise->getMuscleGroups());
        $this->assertEmpty($this->exercise->getMuscleGroups());
    }
}
