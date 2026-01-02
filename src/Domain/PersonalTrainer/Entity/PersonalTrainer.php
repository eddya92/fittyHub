<?php

namespace App\Domain\PersonalTrainer\Entity;

use App\Domain\User\Entity\User;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Workout\Entity\WorkoutPlan;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PersonalTrainer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'personalTrainerProfile', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'internalPTs')]
    private ?Gym $gym = null;

    #[ORM\Column]
    private ?bool $isInternal = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialization = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $certifications = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $hourlyRate = null;

    #[ORM\Column(nullable: true)]
    private ?int $experience = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column]
    private ?bool $isAvailableForNewClients = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PTClientRelation>
     */
    #[ORM\OneToMany(targetEntity: PTClientRelation::class, mappedBy: 'personalTrainer', cascade: ['persist', 'remove'])]
    private Collection $clientRelations;

    /**
     * @var Collection<int, WorkoutPlan>
     */
    #[ORM\OneToMany(targetEntity: WorkoutPlan::class, mappedBy: 'personalTrainer')]
    private Collection $workoutPlans;

    public function __construct()
    {
        $this->clientRelations = new ArrayCollection();
        $this->workoutPlans = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->isActive = true;
        $this->isAvailableForNewClients = true;
        $this->isInternal = false;
        $this->certifications = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

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

    public function isInternal(): ?bool
    {
        return $this->isInternal;
    }

    public function setIsInternal(bool $isInternal): static
    {
        $this->isInternal = $isInternal;

        return $this;
    }

    public function getSpecialization(): ?string
    {
        return $this->specialization;
    }

    public function setSpecialization(?string $specialization): static
    {
        $this->specialization = $specialization;

        return $this;
    }

    public function getCertifications(): ?array
    {
        return $this->certifications;
    }

    public function setCertifications(?array $certifications): static
    {
        $this->certifications = $certifications;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function getHourlyRate(): ?string
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?string $hourlyRate): static
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    public function getExperience(): ?int
    {
        return $this->experience;
    }

    public function setExperience(?int $experience): static
    {
        $this->experience = $experience;

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

    public function isAvailableForNewClients(): ?bool
    {
        return $this->isAvailableForNewClients;
    }

    public function setIsAvailableForNewClients(bool $isAvailableForNewClients): static
    {
        $this->isAvailableForNewClients = $isAvailableForNewClients;

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
     * @return Collection<int, PTClientRelation>
     */
    public function getClientRelations(): Collection
    {
        return $this->clientRelations;
    }

    public function addClientRelation(PTClientRelation $clientRelation): static
    {
        if (!$this->clientRelations->contains($clientRelation)) {
            $this->clientRelations->add($clientRelation);
            $clientRelation->setPersonalTrainer($this);
        }

        return $this;
    }

    public function removeClientRelation(PTClientRelation $clientRelation): static
    {
        if ($this->clientRelations->removeElement($clientRelation)) {
            // set the owning side to null (unless already changed)
            if ($clientRelation->getPersonalTrainer() === $this) {
                $clientRelation->setPersonalTrainer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkoutPlan>
     */
    public function getWorkoutPlans(): Collection
    {
        return $this->workoutPlans;
    }

    public function addWorkoutPlan(WorkoutPlan $workoutPlan): static
    {
        if (!$this->workoutPlans->contains($workoutPlan)) {
            $this->workoutPlans->add($workoutPlan);
            $workoutPlan->setPersonalTrainer($this);
        }

        return $this;
    }

    public function removeWorkoutPlan(WorkoutPlan $workoutPlan): static
    {
        if ($this->workoutPlans->removeElement($workoutPlan)) {
            // set the owning side to null (unless already changed)
            if ($workoutPlan->getPersonalTrainer() === $this) {
                $workoutPlan->setPersonalTrainer(null);
            }
        }

        return $this;
    }
}
