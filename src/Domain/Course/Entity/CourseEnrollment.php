<?php

namespace App\Domain\Course\Entity;

use App\Domain\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'course_enrollments')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['enrollment:read']],
    denormalizationContext: ['groups' => ['enrollment:write']]
)]
class CourseEnrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['enrollment:read', 'course:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GymCourse::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?GymCourse $course = null;

    #[ORM\ManyToOne(targetEntity: CourseSchedule::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?CourseSchedule $schedule = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read', 'enrollment:write', 'course:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Groups(['enrollment:read', 'enrollment:write', 'course:read'])]
    private string $status = 'active'; // active, cancelled, completed

    #[ORM\Column]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeImmutable $enrolledAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->enrolledAt = new \DateTimeImmutable();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getEnrolledAt(): ?\DateTimeImmutable
    {
        return $this->enrolledAt;
    }

    public function setEnrolledAt(\DateTimeImmutable $enrolledAt): static
    {
        $this->enrolledAt = $enrolledAt;
        return $this;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): static
    {
        $this->cancelledAt = $cancelledAt;
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

    public function getSchedule(): ?CourseSchedule
    {
        return $this->schedule;
    }

    public function setSchedule(?CourseSchedule $schedule): static
    {
        $this->schedule = $schedule;
        return $this;
    }
}
