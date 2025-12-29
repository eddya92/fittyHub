<?php

namespace App\Domain\Workout\Entity;

use App\Domain\Workout\Repository\WorkoutExerciseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkoutExerciseRepository::class)]
class WorkoutExercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'exercises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkoutPlan $workoutPlan = null;

    #[ORM\Column]
    private ?int $dayNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $dayLabel = null;

    #[ORM\Column]
    private ?int $orderPosition = null;

    #[ORM\Column(length: 255)]
    private ?string $exerciseName = null;

    #[ORM\Column(length: 50)]
    private ?string $exerciseCategory = null;

    #[ORM\Column(length: 50)]
    private ?string $muscleGroup = null;

    #[ORM\Column]
    private ?int $sets = null;

    #[ORM\Column(length: 50)]
    private ?string $reps = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $weight = null;

    #[ORM\Column(nullable: true)]
    private ?int $restTime = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $tempo = null;

    #[ORM\Column(nullable: true)]
    private ?int $rpe = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkoutPlan(): ?WorkoutPlan
    {
        return $this->workoutPlan;
    }

    public function setWorkoutPlan(?WorkoutPlan $workoutPlan): static
    {
        $this->workoutPlan = $workoutPlan;

        return $this;
    }

    public function getDayNumber(): ?int
    {
        return $this->dayNumber;
    }

    public function setDayNumber(int $dayNumber): static
    {
        $this->dayNumber = $dayNumber;

        return $this;
    }

    public function getDayLabel(): ?string
    {
        return $this->dayLabel;
    }

    public function setDayLabel(string $dayLabel): static
    {
        $this->dayLabel = $dayLabel;

        return $this;
    }

    public function getOrderPosition(): ?int
    {
        return $this->orderPosition;
    }

    public function setOrderPosition(int $orderPosition): static
    {
        $this->orderPosition = $orderPosition;

        return $this;
    }

    public function getExerciseName(): ?string
    {
        return $this->exerciseName;
    }

    public function setExerciseName(string $exerciseName): static
    {
        $this->exerciseName = $exerciseName;

        return $this;
    }

    public function getExerciseCategory(): ?string
    {
        return $this->exerciseCategory;
    }

    public function setExerciseCategory(string $exerciseCategory): static
    {
        $this->exerciseCategory = $exerciseCategory;

        return $this;
    }

    public function getMuscleGroup(): ?string
    {
        return $this->muscleGroup;
    }

    public function setMuscleGroup(string $muscleGroup): static
    {
        $this->muscleGroup = $muscleGroup;

        return $this;
    }

    public function getSets(): ?int
    {
        return $this->sets;
    }

    public function setSets(int $sets): static
    {
        $this->sets = $sets;

        return $this;
    }

    public function getReps(): ?string
    {
        return $this->reps;
    }

    public function setReps(string $reps): static
    {
        $this->reps = $reps;

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

    public function getRestTime(): ?int
    {
        return $this->restTime;
    }

    public function setRestTime(?int $restTime): static
    {
        $this->restTime = $restTime;

        return $this;
    }

    public function getTempo(): ?string
    {
        return $this->tempo;
    }

    public function setTempo(?string $tempo): static
    {
        $this->tempo = $tempo;

        return $this;
    }

    public function getRpe(): ?int
    {
        return $this->rpe;
    }

    public function setRpe(?int $rpe): static
    {
        $this->rpe = $rpe;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

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
}
