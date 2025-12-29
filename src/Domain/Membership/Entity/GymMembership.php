<?php

namespace App\Domain\Membership\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Domain\Membership\Repository\GymMembershipRepository;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\Medical\Entity\MedicalCertificate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GymMembershipRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['membership:read']],
            security: "is_granted('ROLE_USER') and (object.getUser() == user or is_granted('ROLE_GYM_ADMIN'))"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['membership:read']],
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            normalizationContext: ['groups' => ['membership:read']],
            denormalizationContext: ['groups' => ['membership:create']],
            security: "is_granted('ROLE_USER')"
        ),
        new Patch(
            normalizationContext: ['groups' => ['membership:read']],
            denormalizationContext: ['groups' => ['membership:update']],
            security: "is_granted('ROLE_GYM_ADMIN')"
        ),
        new Delete(
            security: "is_granted('ROLE_GYM_ADMIN')"
        ),
    ],
    normalizationContext: ['groups' => ['membership:read']],
    denormalizationContext: ['groups' => ['membership:create', 'membership:update']]
)]
class GymMembership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['membership:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['membership:read', 'membership:create'])]
    private ?Gym $gym = null;

    #[ORM\ManyToOne(inversedBy: 'gymMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['membership:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Groups(['membership:read', 'membership:update'])]
    private ?string $status = 'active'; // active, expired, suspended

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['membership:read', 'membership:create', 'membership:update'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['membership:read', 'membership:create', 'membership:update'])]
    private ?\DateTimeInterface $endDate = null; // "Abbonato fino al..."

    #[ORM\ManyToOne]
    #[Groups(['membership:read', 'membership:update'])]
    private ?PersonalTrainer $assignedPT = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['membership:read', 'membership:create', 'membership:update'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['membership:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['membership:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'gymMembership', cascade: ['persist', 'remove'])]
    private ?MedicalCertificate $medicalCertificate = null;

    /**
     * @var Collection<int, GymAttendance>
     */
    #[ORM\OneToMany(targetEntity: GymAttendance::class, mappedBy: 'gymMembership')]
    private Collection $attendances;

    public function __construct()
    {
        $this->attendances = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = 'pending';
        $this->autoRenew = false;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getAssignedPT(): ?PersonalTrainer
    {
        return $this->assignedPT;
    }

    public function setAssignedPT(?PersonalTrainer $assignedPT): static
    {
        $this->assignedPT = $assignedPT;

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

    public function getMedicalCertificate(): ?MedicalCertificate
    {
        return $this->medicalCertificate;
    }

    public function setMedicalCertificate(?MedicalCertificate $medicalCertificate): static
    {
        // unset the owning side of the relation if necessary
        if ($medicalCertificate === null && $this->medicalCertificate !== null) {
            $this->medicalCertificate->setGymMembership(null);
        }

        // set the owning side of the relation if necessary
        if ($medicalCertificate !== null && $medicalCertificate->getGymMembership() !== $this) {
            $medicalCertificate->setGymMembership($this);
        }

        $this->medicalCertificate = $medicalCertificate;

        return $this;
    }

    /**
     * @return Collection<int, GymAttendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(GymAttendance $attendance): static
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances->add($attendance);
            $attendance->setGymMembership($this);
        }

        return $this;
    }

    public function removeAttendance(GymAttendance $attendance): static
    {
        if ($this->attendances->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getGymMembership() === $this) {
                $attendance->setGymMembership(null);
            }
        }

        return $this;
    }
}
