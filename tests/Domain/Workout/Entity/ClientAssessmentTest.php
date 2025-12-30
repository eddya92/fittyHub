<?php

namespace App\Tests\Domain\Workout\Entity;

use App\Domain\Workout\Entity\ClientAssessment;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use PHPUnit\Framework\TestCase;

class ClientAssessmentTest extends TestCase
{
    private ClientAssessment $assessment;
    private User $client;
    private PersonalTrainer $pt;

    protected function setUp(): void
    {
        $this->assessment = new ClientAssessment();
        $this->client = new User();
        $this->pt = new PersonalTrainer();
    }

    public function testConstructorInitializesDefaults(): void
    {
        $assessment = new ClientAssessment();

        $this->assertEquals('draft', $assessment->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $assessment->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $assessment->getUpdatedAt());
        $this->assertIsArray($assessment->getCurrentInjuries());
        $this->assertIsArray($assessment->getPastInjuries());
        $this->assertIsArray($assessment->getMedicalConditions());
        $this->assertEmpty($assessment->getCurrentInjuries());
    }

    public function testCanSetAndGetClient(): void
    {
        $this->assessment->setClient($this->client);
        $this->assertSame($this->client, $this->assessment->getClient());
    }

    public function testCanSetAndGetPersonalTrainer(): void
    {
        $this->assessment->setPersonalTrainer($this->pt);
        $this->assertSame($this->pt, $this->assessment->getPersonalTrainer());
    }

    public function testCanSetAndGetPhysicalData(): void
    {
        $this->assessment->setAge(30);
        $this->assessment->setHeight('180.5');
        $this->assessment->setWeight('75.5');
        $this->assessment->setGender('M');

        $this->assertEquals(30, $this->assessment->getAge());
        $this->assertEquals('180.5', $this->assessment->getHeight());
        $this->assertEquals('75.5', $this->assessment->getWeight());
        $this->assertEquals('M', $this->assessment->getGender());
    }

    public function testCanSetAndGetFitnessGoals(): void
    {
        $this->assessment->setFitnessLevel('intermediate');
        $this->assessment->setPrimaryGoal('Aumento massa muscolare');
        $this->assessment->setSecondaryGoals('Miglioramento resistenza');
        $this->assessment->setTrainingExperience(5);
        $this->assessment->setWeeklyAvailability(4);
        $this->assessment->setSessionDuration(60);

        $this->assertEquals('intermediate', $this->assessment->getFitnessLevel());
        $this->assertEquals('Aumento massa muscolare', $this->assessment->getPrimaryGoal());
        $this->assertEquals('Miglioramento resistenza', $this->assessment->getSecondaryGoals());
        $this->assertEquals(5, $this->assessment->getTrainingExperience());
        $this->assertEquals(4, $this->assessment->getWeeklyAvailability());
        $this->assertEquals(60, $this->assessment->getSessionDuration());
    }

    public function testCanSetAndGetHealthData(): void
    {
        $currentInjuries = ['Tendinite spalla destra'];
        $pastInjuries = ['Distorsione caviglia 2020'];
        $medicalConditions = ['Ipertensione'];

        $this->assessment->setCurrentInjuries($currentInjuries);
        $this->assessment->setPastInjuries($pastInjuries);
        $this->assessment->setMedicalConditions($medicalConditions);
        $this->assessment->setMedications('Farmaco X 1 volta al giorno');
        $this->assessment->setAllergies('Lattosio');

        $this->assertEquals($currentInjuries, $this->assessment->getCurrentInjuries());
        $this->assertEquals($pastInjuries, $this->assessment->getPastInjuries());
        $this->assertEquals($medicalConditions, $this->assessment->getMedicalConditions());
        $this->assertEquals('Farmaco X 1 volta al giorno', $this->assessment->getMedications());
        $this->assertEquals('Lattosio', $this->assessment->getAllergies());
    }

    public function testCanSetAndGetLifestyleData(): void
    {
        $this->assessment->setActivityLevel('moderate');
        $this->assessment->setOccupation('Lavoro sedentario');
        $this->assessment->setSleepHours(7);
        $this->assessment->setStressLevel(6);
        $this->assessment->setNutritionHabits('3 pasti al giorno, dieta bilanciata');

        $this->assertEquals('moderate', $this->assessment->getActivityLevel());
        $this->assertEquals('Lavoro sedentario', $this->assessment->getOccupation());
        $this->assertEquals(7, $this->assessment->getSleepHours());
        $this->assertEquals(6, $this->assessment->getStressLevel());
        $this->assertEquals('3 pasti al giorno, dieta bilanciata', $this->assessment->getNutritionHabits());
    }

    public function testCanSetAndGetTrainingPreferences(): void
    {
        $preferred = ['Panca piana', 'Squat'];
        $disliked = ['Burpees', 'Corsa'];

        $this->assessment->setPreferredExercises($preferred);
        $this->assessment->setDislikedExercises($disliked);
        $this->assessment->setTrainingPreferences('Preferisco allenamento pesi liberi');

        $this->assertEquals($preferred, $this->assessment->getPreferredExercises());
        $this->assertEquals($disliked, $this->assessment->getDislikedExercises());
        $this->assertEquals('Preferisco allenamento pesi liberi', $this->assessment->getTrainingPreferences());
    }

    public function testCanSetAndGetBodyMeasurements(): void
    {
        $circumferences = [
            'chest' => '100',
            'waist' => '85',
            'hips' => '95',
            'arms' => '35',
            'thighs' => '60'
        ];

        $this->assessment->setBodyFatPercentage('15.5');
        $this->assessment->setMuscleMass('60.5');
        $this->assessment->setCircumferences($circumferences);

        $this->assertEquals('15.5', $this->assessment->getBodyFatPercentage());
        $this->assertEquals('60.5', $this->assessment->getMuscleMass());
        $this->assertEquals($circumferences, $this->assessment->getCircumferences());
    }

    public function testCanSetAndGetPtNotes(): void
    {
        $notes = 'Cliente molto motivato, focus su upper body';
        $this->assessment->setPtNotes($notes);

        $this->assertEquals($notes, $this->assessment->getPtNotes());
    }

    public function testMarkAsCompletedSetsStatusAndTimestamp(): void
    {
        $this->assertEquals('draft', $this->assessment->getStatus());
        $this->assertNull($this->assessment->getCompletedAt());

        $beforeComplete = new \DateTimeImmutable();
        $this->assessment->markAsCompleted();
        $afterComplete = new \DateTimeImmutable();

        $this->assertEquals('completed', $this->assessment->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->assessment->getCompletedAt());
        $this->assertGreaterThanOrEqual($beforeComplete, $this->assessment->getCompletedAt());
        $this->assertLessThanOrEqual($afterComplete, $this->assessment->getCompletedAt());
    }

    public function testMarkAsCompletedUpdatesUpdatedAt(): void
    {
        $originalUpdatedAt = $this->assessment->getUpdatedAt();

        // Small delay to ensure timestamp difference
        usleep(10000);

        $this->assessment->markAsCompleted();

        $this->assertGreaterThan($originalUpdatedAt, $this->assessment->getUpdatedAt());
    }

    public function testCanSetStatus(): void
    {
        $this->assessment->setStatus('completed');
        $this->assertEquals('completed', $this->assessment->getStatus());

        $this->assessment->setStatus('draft');
        $this->assertEquals('draft', $this->assessment->getStatus());
    }

    public function testCanSetStrengthTests(): void
    {
        $strengthTests = [
            'bench_press' => '80kg',
            'squat' => '100kg',
            'deadlift' => '120kg'
        ];

        $this->assessment->setStrengthTests($strengthTests);
        $this->assertEquals($strengthTests, $this->assessment->getStrengthTests());
    }

    public function testCanSetFlexibilityTests(): void
    {
        $flexibilityTests = [
            'sit_and_reach' => '25cm',
            'shoulder_flexibility' => 'good'
        ];

        $this->assessment->setFlexibilityTests($flexibilityTests);
        $this->assertEquals($flexibilityTests, $this->assessment->getFlexibilityTests());
    }

    public function testCanSetAvailableEquipment(): void
    {
        $equipment = ['Bilanciere', 'Manubri', 'Panca'];

        $this->assessment->setAvailableEquipment($equipment);
        $this->assertEquals($equipment, $this->assessment->getAvailableEquipment());
    }

    public function testCompleteAssessmentWorkflow(): void
    {
        // Simula compilazione completa questionario
        $this->assessment->setClient($this->client);
        $this->assessment->setPersonalTrainer($this->pt);
        $this->assessment->setAge(28);
        $this->assessment->setHeight('175');
        $this->assessment->setWeight('70');
        $this->assessment->setGender('M');
        $this->assessment->setFitnessLevel('beginner');
        $this->assessment->setPrimaryGoal('Perdita peso e tonificazione');
        $this->assessment->setWeeklyAvailability(3);
        $this->assessment->setSessionDuration(45);
        $this->assessment->setCurrentInjuries([]);
        $this->assessment->setPastInjuries(['Distorsione polso']);
        $this->assessment->setActivityLevel('sedentary');
        $this->assessment->setSleepHours(6);
        $this->assessment->setStressLevel(7);
        $this->assessment->setPtNotes('Iniziare gradualmente, focus cardio');

        // Verifica stato iniziale
        $this->assertEquals('draft', $this->assessment->getStatus());

        // Completa assessment
        $this->assessment->markAsCompleted();

        // Verifica stato finale
        $this->assertEquals('completed', $this->assessment->getStatus());
        $this->assertNotNull($this->assessment->getCompletedAt());
        $this->assertSame($this->client, $this->assessment->getClient());
        $this->assertSame($this->pt, $this->assessment->getPersonalTrainer());
    }
}