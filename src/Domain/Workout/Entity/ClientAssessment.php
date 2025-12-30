<?php

namespace App\Domain\Workout\Entity;

use App\Domain\Workout\Repository\ClientAssessmentRepository;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientAssessmentRepository::class)]
class ClientAssessment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?PersonalTrainer $personalTrainer = null;

    // Dati anagrafici/fisici
    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $height = null; // in cm

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $weight = null; // in kg

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $gender = null;

    // Esperienza e obiettivi
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fitnessLevel = null; // principiante, intermedio, avanzato

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $primaryGoal = null; // perdita peso, massa muscolare, forza, resistenza, ecc.

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $secondaryGoals = null;

    #[ORM\Column(nullable: true)]
    private ?int $trainingExperience = null; // anni di esperienza

    #[ORM\Column(nullable: true)]
    private ?int $weeklyAvailability = null; // giorni a settimana disponibili

    #[ORM\Column(nullable: true)]
    private ?int $sessionDuration = null; // minuti per sessione

    // Stato di salute
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $currentInjuries = null; // array di infortuni attuali

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $pastInjuries = null; // storico infortuni

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $medicalConditions = null; // condizioni mediche

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $medications = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allergies = null;

    // Stile di vita
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $activityLevel = null; // sedentario, moderato, attivo, molto attivo

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $occupation = null; // tipo di lavoro (sedentario, fisico, ecc.)

    #[ORM\Column(nullable: true)]
    private ?int $sleepHours = null; // ore di sonno medie

    #[ORM\Column(nullable: true)]
    private ?int $stressLevel = null; // 1-10

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $nutritionHabits = null;

    // Preferenze allenamento
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $preferredExercises = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $dislikedExercises = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $availableEquipment = null; // attrezzatura disponibile

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $trainingPreferences = null; // preferenze generali

    // Valutazioni fisiche (opzionali - da fare in palestra)
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $bodyFatPercentage = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $muscleMass = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $circumferences = null; // misure circonferenze (petto, vita, fianchi, braccia, ecc.)

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $strengthTests = null; // test di forza massimale

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $flexibilityTests = null; // test flessibilità

    // Note e status
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ptNotes = null; // note private del PT

    #[ORM\Column(length: 20)]
    private ?string $status = 'draft'; // draft, completed

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = 'draft';
        $this->currentInjuries = [];
        $this->pastInjuries = [];
        $this->medicalConditions = [];
        $this->preferredExercises = [];
        $this->dislikedExercises = [];
        $this->availableEquipment = [];
        $this->circumferences = [];
        $this->strengthTests = [];
        $this->flexibilityTests = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getPersonalTrainer(): ?PersonalTrainer
    {
        return $this->personalTrainer;
    }

    public function setPersonalTrainer(?PersonalTrainer $personalTrainer): static
    {
        $this->personalTrainer = $personalTrainer;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;
        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getFitnessLevel(): ?string
    {
        return $this->fitnessLevel;
    }

    public function setFitnessLevel(?string $fitnessLevel): static
    {
        $this->fitnessLevel = $fitnessLevel;
        return $this;
    }

    public function getPrimaryGoal(): ?string
    {
        return $this->primaryGoal;
    }

    public function setPrimaryGoal(?string $primaryGoal): static
    {
        $this->primaryGoal = $primaryGoal;
        return $this;
    }

    public function getSecondaryGoals(): ?string
    {
        return $this->secondaryGoals;
    }

    public function setSecondaryGoals(?string $secondaryGoals): static
    {
        $this->secondaryGoals = $secondaryGoals;
        return $this;
    }

    public function getTrainingExperience(): ?int
    {
        return $this->trainingExperience;
    }

    public function setTrainingExperience(?int $trainingExperience): static
    {
        $this->trainingExperience = $trainingExperience;
        return $this;
    }

    public function getWeeklyAvailability(): ?int
    {
        return $this->weeklyAvailability;
    }

    public function setWeeklyAvailability(?int $weeklyAvailability): static
    {
        $this->weeklyAvailability = $weeklyAvailability;
        return $this;
    }

    public function getSessionDuration(): ?int
    {
        return $this->sessionDuration;
    }

    public function setSessionDuration(?int $sessionDuration): static
    {
        $this->sessionDuration = $sessionDuration;
        return $this;
    }

    public function getCurrentInjuries(): ?array
    {
        return $this->currentInjuries;
    }

    public function setCurrentInjuries(?array $currentInjuries): static
    {
        $this->currentInjuries = $currentInjuries;
        return $this;
    }

    public function getPastInjuries(): ?array
    {
        return $this->pastInjuries;
    }

    public function setPastInjuries(?array $pastInjuries): static
    {
        $this->pastInjuries = $pastInjuries;
        return $this;
    }

    public function getMedicalConditions(): ?array
    {
        return $this->medicalConditions;
    }

    public function setMedicalConditions(?array $medicalConditions): static
    {
        $this->medicalConditions = $medicalConditions;
        return $this;
    }

    public function getMedications(): ?string
    {
        return $this->medications;
    }

    public function setMedications(?string $medications): static
    {
        $this->medications = $medications;
        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getActivityLevel(): ?string
    {
        return $this->activityLevel;
    }

    public function setActivityLevel(?string $activityLevel): static
    {
        $this->activityLevel = $activityLevel;
        return $this;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function setOccupation(?string $occupation): static
    {
        $this->occupation = $occupation;
        return $this;
    }

    public function getSleepHours(): ?int
    {
        return $this->sleepHours;
    }

    public function setSleepHours(?int $sleepHours): static
    {
        $this->sleepHours = $sleepHours;
        return $this;
    }

    public function getStressLevel(): ?int
    {
        return $this->stressLevel;
    }

    public function setStressLevel(?int $stressLevel): static
    {
        $this->stressLevel = $stressLevel;
        return $this;
    }

    public function getNutritionHabits(): ?string
    {
        return $this->nutritionHabits;
    }

    public function setNutritionHabits(?string $nutritionHabits): static
    {
        $this->nutritionHabits = $nutritionHabits;
        return $this;
    }

    public function getPreferredExercises(): ?array
    {
        return $this->preferredExercises;
    }

    public function setPreferredExercises(?array $preferredExercises): static
    {
        $this->preferredExercises = $preferredExercises;
        return $this;
    }

    public function getDislikedExercises(): ?array
    {
        return $this->dislikedExercises;
    }

    public function setDislikedExercises(?array $dislikedExercises): static
    {
        $this->dislikedExercises = $dislikedExercises;
        return $this;
    }

    public function getAvailableEquipment(): ?array
    {
        return $this->availableEquipment;
    }

    public function setAvailableEquipment(?array $availableEquipment): static
    {
        $this->availableEquipment = $availableEquipment;
        return $this;
    }

    public function getTrainingPreferences(): ?string
    {
        return $this->trainingPreferences;
    }

    public function setTrainingPreferences(?string $trainingPreferences): static
    {
        $this->trainingPreferences = $trainingPreferences;
        return $this;
    }

    public function getBodyFatPercentage(): ?string
    {
        return $this->bodyFatPercentage;
    }

    public function setBodyFatPercentage(?string $bodyFatPercentage): static
    {
        $this->bodyFatPercentage = $bodyFatPercentage;
        return $this;
    }

    public function getMuscleMass(): ?string
    {
        return $this->muscleMass;
    }

    public function setMuscleMass(?string $muscleMass): static
    {
        $this->muscleMass = $muscleMass;
        return $this;
    }

    public function getCircumferences(): ?array
    {
        return $this->circumferences;
    }

    public function setCircumferences(?array $circumferences): static
    {
        $this->circumferences = $circumferences;
        return $this;
    }

    public function getStrengthTests(): ?array
    {
        return $this->strengthTests;
    }

    public function setStrengthTests(?array $strengthTests): static
    {
        $this->strengthTests = $strengthTests;
        return $this;
    }

    public function getFlexibilityTests(): ?array
    {
        return $this->flexibilityTests;
    }

    public function setFlexibilityTests(?array $flexibilityTests): static
    {
        $this->flexibilityTests = $flexibilityTests;
        return $this;
    }

    public function getPtNotes(): ?string
    {
        return $this->ptNotes;
    }

    public function setPtNotes(?string $ptNotes): static
    {
        $this->ptNotes = $ptNotes;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function markAsCompleted(): static
    {
        $this->status = 'completed';
        $this->completedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }
}