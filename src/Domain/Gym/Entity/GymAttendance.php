<?php

namespace App\Domain\Gym\Entity;

use App\Domain\User\Entity\User;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Course\Entity\CourseSession;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class GymAttendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gym $gym = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'attendances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GymMembership $gymMembership = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $checkInTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $checkOutTime = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 20, options: ['default' => 'gym_entrance'])]
    private string $type = 'gym_entrance'; // gym_entrance | course

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?CourseSession $courseSession = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->checkInTime = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGymMembership(): ?GymMembership
    {
        return $this->gymMembership;
    }

    public function setGymMembership(?GymMembership $gymMembership): static
    {
        $this->gymMembership = $gymMembership;

        return $this;
    }

    public function getCheckInTime(): ?\DateTimeInterface
    {
        return $this->checkInTime;
    }

    public function setCheckInTime(\DateTimeInterface $checkInTime): static
    {
        $this->checkInTime = $checkInTime;

        return $this;
    }

    public function getCheckOutTime(): ?\DateTimeInterface
    {
        return $this->checkOutTime;
    }

    public function setCheckOutTime(?\DateTimeInterface $checkOutTime): static
    {
        $this->checkOutTime = $checkOutTime;

        // Calculate duration in minutes if both check-in and check-out are set
        if ($this->checkInTime && $checkOutTime) {
            $interval = $this->checkInTime->diff($checkOutTime);
            $this->duration = ($interval->h * 60) + $interval->i;
        }

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getCourseSession(): ?CourseSession
    {
        return $this->courseSession;
    }

    public function setCourseSession(?CourseSession $courseSession): static
    {
        $this->courseSession = $courseSession;

        return $this;
    }
}
