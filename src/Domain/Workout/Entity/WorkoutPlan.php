<?php

namespace App\Domain\Workout\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Domain\Workout\Repository\WorkoutPlanRepository;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WorkoutPlanRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['workout:read', 'workout:read:full']],
            security: "is_granted('ROLE_USER') and (object.getClient() == user or object.getPersonalTrainer()?.getUser() == user)"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['workout:read']],
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            normalizationContext: ['groups' => ['workout:read']],
            denormalizationContext: ['groups' => ['workout:create']],
            security: "is_granted('ROLE_USER')"
        ),
        new Put(
            normalizationContext: ['groups' => ['workout:read']],
            denormalizationContext: ['groups' => ['workout:update']],
            security: "is_granted('ROLE_USER') and (object.getClient() == user or object.getPersonalTrainer()?.getUser() == user)"
        ),
        new Patch(
            normalizationContext: ['groups' => ['workout:read']],
            denormalizationContext: ['groups' => ['workout:update']],
            security: "is_granted('ROLE_USER') and (object.getClient() == user or object.getPersonalTrainer()?.getUser() == user)"
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.getClient() == user"
        ),
    ],
    normalizationContext: ['groups' => ['workout:read']],
    denormalizationContext: ['groups' => ['workout:create', 'workout:update']]
)]
class WorkoutPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['workout:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['workout:read', 'workout:create', 'workout:update'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['workout:read', 'workout:create', 'workout:update'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'workoutPlans')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['workout:read:full'])]
    private ?PersonalTrainer $personalTrainer = null;

    #[ORM\ManyToOne(inversedBy: 'workoutPlans')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['workout:read'])]
    private ?User $client = null;

    #[ORM\Column(length: 50)]
    #[Groups(['workout:read', 'workout:create'])]
    private ?string $planType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['workout:read', 'workout:create', 'workout:update'])]
    private ?string $goal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['workout:read', 'workout:create', 'workout:update'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['workout:read', 'workout:create', 'workout:update'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Groups(['workout:read', 'workout:create', 'workout:update'])]
    private ?int $weeksCount = null;

    #[ORM\Column]
    #[Groups(['workout:read', 'workout:update'])]
    private ?bool $isActive = true;

    #[ORM\Column]
    #[Groups(['workout:read'])]
    private ?bool $isTemplate = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['workout:read', 'workout:create', 'workout:update'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['workout:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['workout:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, WorkoutExercise>
     */
    #[ORM\OneToMany(targetEntity: WorkoutExercise::class, mappedBy: 'workoutPlan', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dayNumber' => 'ASC', 'orderPosition' => 'ASC'])]
    private Collection $exercises;

    /**
     * @var Collection<int, WorkoutSession>
     */
    #[ORM\OneToMany(targetEntity: WorkoutSession::class, mappedBy: 'workoutPlan', cascade: ['persist', 'remove'])]
    private Collection $sessions;

    public function __construct()
    {
        $this->exercises = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->isActive = true;
        $this->isTemplate = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getPlanType(): ?string
    {
        return $this->planType;
    }

    public function setPlanType(string $planType): static
    {
        $this->planType = $planType;

        return $this;
    }

    public function getGoal(): ?string
    {
        return $this->goal;
    }

    public function setGoal(?string $goal): static
    {
        $this->goal = $goal;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getWeeksCount(): ?int
    {
        return $this->weeksCount;
    }

    public function setWeeksCount(int $weeksCount): static
    {
        $this->weeksCount = $weeksCount;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isTemplate(): ?bool
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(bool $isTemplate): static
    {
        $this->isTemplate = $isTemplate;

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

    /**
     * @return Collection<int, WorkoutExercise>
     */
    public function getExercises(): Collection
    {
        return $this->exercises;
    }

    public function addExercise(WorkoutExercise $exercise): static
    {
        if (!$this->exercises->contains($exercise)) {
            $this->exercises->add($exercise);
            $exercise->setWorkoutPlan($this);
        }

        return $this;
    }

    public function removeExercise(WorkoutExercise $exercise): static
    {
        if ($this->exercises->removeElement($exercise)) {
            // set the owning side to null (unless already changed)
            if ($exercise->getWorkoutPlan() === $this) {
                $exercise->setWorkoutPlan(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkoutSession>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(WorkoutSession $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setWorkoutPlan($this);
        }

        return $this;
    }

    public function removeSession(WorkoutSession $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getWorkoutPlan() === $this) {
                $session->setWorkoutPlan(null);
            }
        }

        return $this;
    }
}
