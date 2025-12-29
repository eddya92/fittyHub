<?php

namespace App\Domain\User\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\State\UserPasswordHasher;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\Workout\Entity\WorkoutPlan;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['user:read', 'user:read:full']],
            security: "is_granted('ROLE_USER') and object == user"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']],
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:create']],
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Put(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:update']],
            security: "is_granted('ROLE_USER') and object == user"
        ),
        new Patch(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:update']],
            security: "is_granted('ROLE_USER') and object == user"
        ),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create', 'user:update']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:create'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read:full'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:create'])]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $gender = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $city = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:update'])]
    private ?string $profileImage = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, GymMembership>
     */
    #[ORM\OneToMany(targetEntity: GymMembership::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $gymMemberships;

    /**
     * @var Collection<int, MedicalCertificate>
     */
    #[ORM\OneToMany(targetEntity: MedicalCertificate::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $medicalCertificates;

    #[ORM\OneToOne(targetEntity: PersonalTrainer::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?PersonalTrainer $personalTrainerProfile = null;

    /**
     * @var Collection<int, WorkoutPlan>
     */
    #[ORM\OneToMany(targetEntity: WorkoutPlan::class, mappedBy: 'client')]
    private Collection $workoutPlans;

    public function __construct()
    {
        $this->gymMemberships = new ArrayCollection();
        $this->medicalCertificates = new ArrayCollection();
        $this->workoutPlans = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): static
    {
        $this->profileImage = $profileImage;

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
     * @return Collection<int, GymMembership>
     */
    public function getGymMemberships(): Collection
    {
        return $this->gymMemberships;
    }

    public function addGymMembership(GymMembership $gymMembership): static
    {
        if (!$this->gymMemberships->contains($gymMembership)) {
            $this->gymMemberships->add($gymMembership);
            $gymMembership->setUser($this);
        }

        return $this;
    }

    public function removeGymMembership(GymMembership $gymMembership): static
    {
        if ($this->gymMemberships->removeElement($gymMembership)) {
            // set the owning side to null (unless already changed)
            if ($gymMembership->getUser() === $this) {
                $gymMembership->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalCertificate>
     */
    public function getMedicalCertificates(): Collection
    {
        return $this->medicalCertificates;
    }

    public function addMedicalCertificate(MedicalCertificate $medicalCertificate): static
    {
        if (!$this->medicalCertificates->contains($medicalCertificate)) {
            $this->medicalCertificates->add($medicalCertificate);
            $medicalCertificate->setUser($this);
        }

        return $this;
    }

    public function removeMedicalCertificate(MedicalCertificate $medicalCertificate): static
    {
        if ($this->medicalCertificates->removeElement($medicalCertificate)) {
            // set the owning side to null (unless already changed)
            if ($medicalCertificate->getUser() === $this) {
                $medicalCertificate->setUser(null);
            }
        }

        return $this;
    }

    public function getPersonalTrainerProfile(): ?PersonalTrainer
    {
        return $this->personalTrainerProfile;
    }

    public function setPersonalTrainerProfile(?PersonalTrainer $personalTrainerProfile): static
    {
        // unset the owning side of the relation if necessary
        if ($personalTrainerProfile === null && $this->personalTrainerProfile !== null) {
            $this->personalTrainerProfile->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($personalTrainerProfile !== null && $personalTrainerProfile->getUser() !== $this) {
            $personalTrainerProfile->setUser($this);
        }

        $this->personalTrainerProfile = $personalTrainerProfile;

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
            $workoutPlan->setClient($this);
        }

        return $this;
    }

    public function removeWorkoutPlan(WorkoutPlan $workoutPlan): static
    {
        if ($this->workoutPlans->removeElement($workoutPlan)) {
            // set the owning side to null (unless already changed)
            if ($workoutPlan->getClient() === $this) {
                $workoutPlan->setClient(null);
            }
        }

        return $this;
    }
}
