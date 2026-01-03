<?php

namespace App\Domain\Course\Entity;

use App\Domain\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'course_waiting_lists')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['waiting_list:read']],
    denormalizationContext: ['groups' => ['waiting_list:write']]
)]
class CourseWaitingList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['waiting_list:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CourseSchedule::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['waiting_list:read', 'waiting_list:write'])]
    private ?CourseSchedule $schedule = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['waiting_list:read', 'waiting_list:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Groups(['waiting_list:read'])]
    private string $status = 'waiting'; // waiting, enrolled, cancelled

    #[ORM\Column]
    #[Groups(['waiting_list:read'])]
    private int $position = 1; // Posizione nella lista d'attesa

    #[ORM\Column]
    #[Groups(['waiting_list:read'])]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['waiting_list:read'])]
    private ?\DateTimeImmutable $notifiedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['waiting_list:read'])]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    #[Groups(['waiting_list:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->joinedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        // La posizione nella lista d'attesa scade dopo 24 ore se non confermata
        $this->expiresAt = (new \DateTimeImmutable())->modify('+24 hours');
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

    public function getSchedule(): ?CourseSchedule
    {
        return $this->schedule;
    }

    public function setSchedule(?CourseSchedule $schedule): static
    {
        $this->schedule = $schedule;
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;
        return $this;
    }

    public function getNotifiedAt(): ?\DateTimeImmutable
    {
        return $this->notifiedAt;
    }

    public function setNotifiedAt(?\DateTimeImmutable $notifiedAt): static
    {
        $this->notifiedAt = $notifiedAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
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

    public function isExpired(): bool
    {
        return $this->expiresAt && $this->expiresAt < new \DateTimeImmutable();
    }

    public function markAsNotified(): void
    {
        $this->notifiedAt = new \DateTimeImmutable();
    }
}