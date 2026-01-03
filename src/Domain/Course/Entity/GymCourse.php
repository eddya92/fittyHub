<?php

namespace App\Domain\Course\Entity;

use App\Domain\Gym\Entity\Gym;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'gym_courses')]
#[ApiResource(
    normalizationContext: ['groups' => ['course:read']],
    denormalizationContext: ['groups' => ['course:write']]
)]
class GymCourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['course:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: CourseCategory::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?CourseCategory $category = null;

    #[ORM\ManyToOne(targetEntity: Gym::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['course:read', 'course:write'])]
    private ?Gym $gym = null;

    #[ORM\ManyToOne(targetEntity: PersonalTrainer::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?PersonalTrainer $instructor = null;

    #[ORM\Column]
    #[Groups(['course:read', 'course:write'])]
    private ?int $maxParticipants = null;

    #[ORM\Column(length: 50)]
    #[Groups(['course:read', 'course:write'])]
    private string $status = 'active'; // active, suspended, cancelled

    #[ORM\Column]
    #[Groups(['course:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['course:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: CourseSchedule::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['course:read'])]
    private Collection $schedules;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: CourseEnrollment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['course:read'])]
    private Collection $enrollments;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
        $this->enrollments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getCategory(): ?CourseCategory
    {
        return $this->category;
    }

    public function setCategory(?CourseCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getGym(): ?Gym
    {
        return $this->gym;
    }

    public function setGym(?Gym $gym): static
    {
        $this->gym = $gym;
        return $this;
    }

    public function getInstructor(): ?PersonalTrainer
    {
        return $this->instructor;
    }

    public function setInstructor(?PersonalTrainer $instructor): static
    {
        $this->instructor = $instructor;
        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;
        return $this;
    }

    public function getStatus(): string
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

    /**
     * @return Collection<int, CourseSchedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(CourseSchedule $schedule): static
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setCourse($this);
        }

        return $this;
    }

    public function removeSchedule(CourseSchedule $schedule): static
    {
        if ($this->schedules->removeElement($schedule)) {
            if ($schedule->getCourse() === $this) {
                $schedule->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CourseEnrollment>
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function addEnrollment(CourseEnrollment $enrollment): static
    {
        if (!$this->enrollments->contains($enrollment)) {
            $this->enrollments->add($enrollment);
            $enrollment->setCourse($this);
        }

        return $this;
    }

    public function removeEnrollment(CourseEnrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            if ($enrollment->getCourse() === $this) {
                $enrollment->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * Conta il totale di iscrizioni attive attraverso TUTTI gli orari del corso
     * (un utente può essere iscritto a più orari)
     */
    public function getActiveEnrollmentsCount(): int
    {
        $total = 0;
        foreach ($this->schedules as $schedule) {
            $total += $schedule->getActiveEnrollmentsCount();
        }
        return $total;
    }

    /**
     * Verifica se ALMENO UN orario ha posti disponibili
     */
    public function hasAvailableSpots(): bool
    {
        foreach ($this->schedules as $schedule) {
            if ($schedule->hasAvailableSpots()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se il corso è attivo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
