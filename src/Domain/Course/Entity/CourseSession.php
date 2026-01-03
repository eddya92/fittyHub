<?php

namespace App\Domain\Course\Entity;

use App\Domain\Gym\Entity\Gym;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Rappresenta una sessione specifica di un corso in una data precisa
 * Es: "Zumba del Lunedì 6 Gennaio 2026 alle 18:00"
 */
#[ORM\Entity]
#[ORM\Table(name: 'course_sessions')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['session:read']],
    denormalizationContext: ['groups' => ['session:write']]
)]
class CourseSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['session:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GymCourse::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['session:read', 'session:write'])]
    private ?GymCourse $course = null;

    #[ORM\ManyToOne(targetEntity: CourseSchedule::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['session:read', 'session:write'])]
    private ?CourseSchedule $schedule = null;

    /**
     * Data specifica di questa sessione (es. 2026-01-06 per il Lunedì 6 Gennaio)
     */
    #[ORM\Column(type: 'date')]
    #[Groups(['session:read', 'session:write'])]
    private ?\DateTimeInterface $sessionDate = null;

    #[ORM\Column(length: 50)]
    #[Groups(['session:read', 'session:write'])]
    private string $status = 'scheduled'; // scheduled, completed, cancelled

    /**
     * Max partecipanti per questa sessione specifica
     * (può essere diverso dal default del corso, es. sessione straordinaria con più posti)
     */
    #[ORM\Column(nullable: true)]
    #[Groups(['session:read', 'session:write'])]
    private ?int $maxParticipants = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['session:read', 'session:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['session:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['session:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: CourseEnrollment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['session:read'])]
    private Collection $enrollments;

    public function __construct()
    {
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

    public function getCourse(): ?GymCourse
    {
        return $this->course;
    }

    public function setCourse(?GymCourse $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getSchedule(): ?CourseSchedule
    {
        return $this->schedule;
    }

    public function setSchedule(?CourseSchedule $schedule): static
    {
        $this->schedule = $schedule;
        return $this;
    }

    public function getSessionDate(): ?\DateTimeInterface
    {
        return $this->sessionDate;
    }

    public function setSessionDate(\DateTimeInterface $sessionDate): static
    {
        $this->sessionDate = $sessionDate;
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

    public function getMaxParticipants(): ?int
    {
        // Se non specificato, usa il max del corso
        return $this->maxParticipants ?? $this->course?->getMaxParticipants();
    }

    public function setMaxParticipants(?int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;
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
            $enrollment->setSession($this);
        }
        return $this;
    }

    public function removeEnrollment(CourseEnrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            if ($enrollment->getSession() === $this) {
                $enrollment->setSession(null);
            }
        }
        return $this;
    }

    /**
     * Conta iscrizioni attive per questa sessione
     */
    public function getActiveEnrollmentsCount(): int
    {
        return $this->enrollments->filter(function(CourseEnrollment $enrollment) {
            return $enrollment->getStatus() === 'active';
        })->count();
    }

    /**
     * Verifica se ci sono posti disponibili
     */
    public function hasAvailableSpots(): bool
    {
        $max = $this->getMaxParticipants();
        return $max === null || $this->getActiveEnrollmentsCount() < $max;
    }

    /**
     * Verifica se la sessione è al completo
     */
    public function isFull(): bool
    {
        return !$this->hasAvailableSpots();
    }

    /**
     * Combina data sessione + orario per ottenere DateTime completo
     */
    public function getDateTime(): ?\DateTimeInterface
    {
        if (!$this->sessionDate || !$this->schedule) {
            return null;
        }

        $date = $this->sessionDate instanceof \DateTimeImmutable
            ? \DateTime::createFromImmutable($this->sessionDate)
            : clone $this->sessionDate;

        $startTime = $this->schedule->getStartTime();
        $date->setTime(
            (int)$startTime->format('H'),
            (int)$startTime->format('i'),
            (int)$startTime->format('s')
        );

        return $date;
    }

    /**
     * Verifica se la sessione è già passata
     */
    public function isInPast(): bool
    {
        $dateTime = $this->getDateTime();
        return $dateTime && $dateTime < new \DateTime();
    }

    /**
     * Verifica se la sessione è nel futuro
     */
    public function isInFuture(): bool
    {
        $dateTime = $this->getDateTime();
        return $dateTime && $dateTime > new \DateTime();
    }

    /**
     * Verifica se la sessione è oggi
     */
    public function isToday(): bool
    {
        if (!$this->sessionDate) {
            return false;
        }

        $today = new \DateTime('today');
        $sessionDay = $this->sessionDate instanceof \DateTimeImmutable
            ? \DateTime::createFromImmutable($this->sessionDate)
            : clone $this->sessionDate;
        $sessionDay->setTime(0, 0, 0);

        return $today->format('Y-m-d') === $sessionDay->format('Y-m-d');
    }

    /**
     * Ottieni posti rimanenti
     */
    public function getAvailableSpots(): int
    {
        $max = $this->getMaxParticipants();
        if ($max === null) {
            return PHP_INT_MAX;
        }
        return max(0, $max - $this->getActiveEnrollmentsCount());
    }
}