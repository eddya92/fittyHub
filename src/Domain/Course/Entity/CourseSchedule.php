<?php

namespace App\Domain\Course\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'course_schedules')]
#[ApiResource(
    normalizationContext: ['groups' => ['schedule:read']],
    denormalizationContext: ['groups' => ['schedule:write']]
)]
class CourseSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['schedule:read', 'course:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GymCourse::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?GymCourse $course = null;

    #[ORM\Column(length: 20)]
    #[Groups(['schedule:read', 'schedule:write', 'course:read'])]
    private ?string $dayOfWeek = null; // monday, tuesday, wednesday, etc.

    #[ORM\Column(type: 'time')]
    #[Groups(['schedule:read', 'schedule:write', 'course:read'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'time')]
    #[Groups(['schedule:read', 'schedule:write', 'course:read'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column]
    #[Groups(['schedule:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: CourseEnrollment::class, mappedBy: 'schedule', cascade: ['remove'])]
    private Collection $enrollments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->enrollments = new ArrayCollection();
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

    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;
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
            $enrollment->setSchedule($this);
        }
        return $this;
    }

    public function removeEnrollment(CourseEnrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            if ($enrollment->getSchedule() === $this) {
                $enrollment->setSchedule(null);
            }
        }
        return $this;
    }

    /**
     * Conta iscrizioni attive per questo specifico orario
     */
    public function getActiveEnrollmentsCount(): int
    {
        return $this->enrollments->filter(function(CourseEnrollment $enrollment) {
            return $enrollment->getStatus() === 'active';
        })->count();
    }

    /**
     * Verifica se ci sono posti disponibili per questo orario
     */
    public function hasAvailableSpots(): bool
    {
        $maxParticipants = $this->course?->getMaxParticipants() ?? 0;
        return $this->getActiveEnrollmentsCount() < $maxParticipants;
    }
}
