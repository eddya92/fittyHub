<?php

namespace App\Domain\Gym\Entity;

use App\Domain\Gym\Repository\GymSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GymSettingsRepository::class)]
#[ORM\Table(name: 'gym_settings')]
class GymSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Gym::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gym $gym = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $courseScheduleStart = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $courseScheduleEnd = null;

    #[ORM\Column(type: 'integer')]
    private int $timeSlotDuration = 60; // minuti

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        // Defaults: 7:00 - 22:00
        $this->courseScheduleStart = new \DateTime('07:00');
        $this->courseScheduleEnd = new \DateTime('22:00');
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGym(): ?Gym
    {
        return $this->gym;
    }

    public function setGym(Gym $gym): self
    {
        $this->gym = $gym;
        return $this;
    }

    public function getCourseScheduleStart(): ?\DateTimeInterface
    {
        return $this->courseScheduleStart;
    }

    public function setCourseScheduleStart(\DateTimeInterface $courseScheduleStart): self
    {
        $this->courseScheduleStart = $courseScheduleStart;
        return $this;
    }

    public function getCourseScheduleEnd(): ?\DateTimeInterface
    {
        return $this->courseScheduleEnd;
    }

    public function setCourseScheduleEnd(\DateTimeInterface $courseScheduleEnd): self
    {
        $this->courseScheduleEnd = $courseScheduleEnd;
        return $this;
    }

    public function getTimeSlotDuration(): int
    {
        return $this->timeSlotDuration;
    }

    public function setTimeSlotDuration(int $timeSlotDuration): self
    {
        $this->timeSlotDuration = $timeSlotDuration;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Genera array di fasce orarie basate su start, end e durata slot
     */
    public function getTimeSlots(): array
    {
        $slots = [];
        $start = clone $this->courseScheduleStart;
        $end = clone $this->courseScheduleEnd;

        $current = clone $start;
        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->modify("+{$this->timeSlotDuration} minutes");
        }

        return $slots;
    }
}
