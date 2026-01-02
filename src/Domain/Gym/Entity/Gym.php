<?php

namespace App\Domain\Gym\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Domain\Gym\Repository\GymRepository;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Entity\SubscriptionPlan;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GymRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'city' => 'exact', 'postalCode' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['name', 'city', 'createdAt'])]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['gym:read', 'gym:read:full']],
            security: "is_granted('ROLE_USER')"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['gym:read']],
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            normalizationContext: ['groups' => ['gym:read']],
            denormalizationContext: ['groups' => ['gym:create']],
            security: "is_granted('ROLE_USER')"
        ),
        new Put(
            normalizationContext: ['groups' => ['gym:read']],
            denormalizationContext: ['groups' => ['gym:update']],
            security: "is_granted('ROLE_GYM_ADMIN')"
        ),
        new Patch(
            normalizationContext: ['groups' => ['gym:read']],
            denormalizationContext: ['groups' => ['gym:update']],
            security: "is_granted('ROLE_GYM_ADMIN')"
        ),
        new Delete(
            security: "is_granted('ROLE_SUPER_ADMIN')"
        ),
    ],
    normalizationContext: ['groups' => ['gym:read']],
    denormalizationContext: ['groups' => ['gym:create', 'gym:update']]
)]
class Gym
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['gym:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $city = null;

    #[ORM\Column(length: 10)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $province = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['gym:read', 'gym:create', 'gym:update'])]
    private ?string $website = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $vatNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $openingHours = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $amenities = null;

    #[ORM\Column]
    #[Groups(['gym:read'])]
    private ?bool $isActive = true;

    // Billing & Subscription SaaS (chi paga per usare la piattaforma)
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['gym:read:full', 'gym:update'])]
    private ?string $subscriptionPlan = null; // starter, professional, enterprise

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['gym:read:full', 'gym:update'])]
    private ?string $subscriptionStatus = null; // trial, active, suspended, cancelled

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $subscriptionStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $subscriptionEndDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxClients = null; // limite clienti per piano

    #[ORM\Column(nullable: true)]
    private ?int $currentClientsCount = 0;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $billingEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $paymentMethod = null; // card, bank_transfer, etc.

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastPaymentDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $nextPaymentDate = null;

    // Enrollment Fee Configuration (Quote Iscrizione/Affiliazione)
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $enrollmentFeeAmount = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $enrollmentFeeType = 'manual'; // one_time, annual, manual

    #[ORM\Column(nullable: true)]
    private ?int $enrollmentFeeValidityMonths = 12; // Validità in mesi (default 12 per annuale)

    #[ORM\Column(nullable: true)]
    private ?bool $showEnrollmentInRenewal = false; // Se mostrare nel processo rinnovo

    #[ORM\Column]
    #[Groups(['gym:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['gym:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'gym_admins')]
    private Collection $admins;

    /**
     * @var Collection<int, PersonalTrainer>
     */
    #[ORM\OneToMany(targetEntity: PersonalTrainer::class, mappedBy: 'gym')]
    private Collection $internalPTs;

    /**
     * @var Collection<int, GymMembership>
     */
    #[ORM\OneToMany(targetEntity: GymMembership::class, mappedBy: 'gym', cascade: ['persist', 'remove'])]
    private Collection $memberships;

    /**
     * @var Collection<int, SubscriptionPlan>
     */
    #[ORM\OneToMany(targetEntity: SubscriptionPlan::class, mappedBy: 'gym', cascade: ['persist', 'remove'])]
    private Collection $subscriptionPlans;

    public function __construct()
    {
        $this->admins = new ArrayCollection();
        $this->internalPTs = new ArrayCollection();
        $this->memberships = new ArrayCollection();
        $this->subscriptionPlans = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->isActive = true;
        $this->amenities = [];
        $this->openingHours = [];
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): static
    {
        $this->province = $province;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): static
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getOpeningHours(): ?array
    {
        return $this->openingHours;
    }

    public function setOpeningHours(?array $openingHours): static
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    public function getAmenities(): ?array
    {
        return $this->amenities;
    }

    public function setAmenities(?array $amenities): static
    {
        $this->amenities = $amenities;

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
     * @return Collection<int, User>
     */
    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(User $admin): static
    {
        if (!$this->admins->contains($admin)) {
            $this->admins->add($admin);
        }

        return $this;
    }

    public function removeAdmin(User $admin): static
    {
        $this->admins->removeElement($admin);

        return $this;
    }

    /**
     * @return Collection<int, PersonalTrainer>
     */
    public function getInternalPTs(): Collection
    {
        return $this->internalPTs;
    }

    public function addInternalPT(PersonalTrainer $internalPT): static
    {
        if (!$this->internalPTs->contains($internalPT)) {
            $this->internalPTs->add($internalPT);
            $internalPT->setGym($this);
        }

        return $this;
    }

    public function removeInternalPT(PersonalTrainer $internalPT): static
    {
        if ($this->internalPTs->removeElement($internalPT)) {
            // set the owning side to null (unless already changed)
            if ($internalPT->getGym() === $this) {
                $internalPT->setGym(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GymMembership>
     */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function addMembership(GymMembership $membership): static
    {
        if (!$this->memberships->contains($membership)) {
            $this->memberships->add($membership);
            $membership->setGym($this);
        }

        return $this;
    }

    public function removeMembership(GymMembership $membership): static
    {
        if ($this->memberships->removeElement($membership)) {
            // set the owning side to null (unless already changed)
            if ($membership->getGym() === $this) {
                $membership->setGym(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SubscriptionPlan>
     */
    public function getSubscriptionPlans(): Collection
    {
        return $this->subscriptionPlans;
    }

    public function addSubscriptionPlan(SubscriptionPlan $subscriptionPlan): static
    {
        if (!$this->subscriptionPlans->contains($subscriptionPlan)) {
            $this->subscriptionPlans->add($subscriptionPlan);
            $subscriptionPlan->setGym($this);
        }

        return $this;
    }

    public function removeSubscriptionPlan(SubscriptionPlan $subscriptionPlan): static
    {
        if ($this->subscriptionPlans->removeElement($subscriptionPlan)) {
            // set the owning side to null (unless already changed)
            if ($subscriptionPlan->getGym() === $this) {
                $subscriptionPlan->setGym(null);
            }
        }

        return $this;
    }

    // Billing & Subscription Getters/Setters
    public function getSubscriptionPlan(): ?string
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(?string $subscriptionPlan): static
    {
        $this->subscriptionPlan = $subscriptionPlan;
        return $this;
    }

    public function getSubscriptionStatus(): ?string
    {
        return $this->subscriptionStatus;
    }

    public function setSubscriptionStatus(?string $subscriptionStatus): static
    {
        $this->subscriptionStatus = $subscriptionStatus;
        return $this;
    }

    public function getSubscriptionStartDate(): ?\DateTimeInterface
    {
        return $this->subscriptionStartDate;
    }

    public function setSubscriptionStartDate(?\DateTimeInterface $subscriptionStartDate): static
    {
        $this->subscriptionStartDate = $subscriptionStartDate;
        return $this;
    }

    public function getSubscriptionEndDate(): ?\DateTimeInterface
    {
        return $this->subscriptionEndDate;
    }

    public function setSubscriptionEndDate(?\DateTimeInterface $subscriptionEndDate): static
    {
        $this->subscriptionEndDate = $subscriptionEndDate;
        return $this;
    }

    public function getMaxClients(): ?int
    {
        return $this->maxClients;
    }

    public function setMaxClients(?int $maxClients): static
    {
        $this->maxClients = $maxClients;
        return $this;
    }

    public function getCurrentClientsCount(): ?int
    {
        return $this->currentClientsCount;
    }

    public function setCurrentClientsCount(?int $currentClientsCount): static
    {
        $this->currentClientsCount = $currentClientsCount;
        return $this;
    }

    public function getBillingEmail(): ?string
    {
        return $this->billingEmail;
    }

    public function setBillingEmail(?string $billingEmail): static
    {
        $this->billingEmail = $billingEmail;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getLastPaymentDate(): ?\DateTimeInterface
    {
        return $this->lastPaymentDate;
    }

    public function setLastPaymentDate(?\DateTimeInterface $lastPaymentDate): static
    {
        $this->lastPaymentDate = $lastPaymentDate;
        return $this;
    }

    public function getNextPaymentDate(): ?\DateTimeInterface
    {
        return $this->nextPaymentDate;
    }

    public function setNextPaymentDate(?\DateTimeInterface $nextPaymentDate): static
    {
        $this->nextPaymentDate = $nextPaymentDate;
        return $this;
    }

    // Enrollment Fee Configuration Getters/Setters
    public function getEnrollmentFeeAmount(): ?string
    {
        return $this->enrollmentFeeAmount;
    }

    public function setEnrollmentFeeAmount(?string $enrollmentFeeAmount): static
    {
        $this->enrollmentFeeAmount = $enrollmentFeeAmount;
        return $this;
    }

    public function getEnrollmentFeeType(): ?string
    {
        return $this->enrollmentFeeType;
    }

    public function setEnrollmentFeeType(?string $enrollmentFeeType): static
    {
        $this->enrollmentFeeType = $enrollmentFeeType;
        return $this;
    }

    public function getEnrollmentFeeValidityMonths(): ?int
    {
        return $this->enrollmentFeeValidityMonths;
    }

    public function setEnrollmentFeeValidityMonths(?int $enrollmentFeeValidityMonths): static
    {
        $this->enrollmentFeeValidityMonths = $enrollmentFeeValidityMonths;
        return $this;
    }

    public function getShowEnrollmentInRenewal(): ?bool
    {
        return $this->showEnrollmentInRenewal;
    }

    public function setShowEnrollmentInRenewal(?bool $showEnrollmentInRenewal): static
    {
        $this->showEnrollmentInRenewal = $showEnrollmentInRenewal;
        return $this;
    }
}
