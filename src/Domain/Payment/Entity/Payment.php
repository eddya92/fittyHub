<?php

namespace App\Domain\Payment\Entity;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gym $gym = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\Column(length: 50)]
    private ?string $paymentMethod = null; // cash, card, bank_transfer, other

    #[ORM\Column(length: 50)]
    private ?string $paymentType = null; // membership, course_enrollment, pt_session, other

    #[ORM\ManyToOne]
    private ?GymMembership $membership = null;

    #[ORM\ManyToOne]
    private ?CourseEnrollment $courseEnrollment = null;

    // Future: PT Session when implemented
    // #[ORM\ManyToOne]
    // private ?PTSession $ptSession = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $transactionReference = null; // Numero ricevuta, bonifico, etc.

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?User $createdBy = null; // Admin che ha registrato il pagamento

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->paymentDate = new \DateTime();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGym(): ?Gym
    {
        return $this->gym;
    }

    public function setGym(?Gym $gym): static
    {
        $this->gym = $gym;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): static
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): static
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getMembership(): ?GymMembership
    {
        return $this->membership;
    }

    public function setMembership(?GymMembership $membership): static
    {
        $this->membership = $membership;

        return $this;
    }

    public function getCourseEnrollment(): ?CourseEnrollment
    {
        return $this->courseEnrollment;
    }

    public function setCourseEnrollment(?CourseEnrollment $courseEnrollment): static
    {
        $this->courseEnrollment = $courseEnrollment;

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

    public function getTransactionReference(): ?string
    {
        return $this->transactionReference;
    }

    public function setTransactionReference(?string $transactionReference): static
    {
        $this->transactionReference = $transactionReference;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get a human-readable description of what this payment is for
     */
    public function getDescription(): string
    {
        return match($this->paymentType) {
            'membership' => $this->membership
                ? sprintf('Abbonamento %s', $this->membership->getSubscriptionPlan()?->getName() ?? 'Standard')
                : 'Abbonamento',
            'course_enrollment' => $this->courseEnrollment
                ? sprintf('Corso: %s', $this->courseEnrollment->getCourseSession()?->getSchedule()?->getCourse()?->getName() ?? 'Corso')
                : 'Iscrizione Corso',
            'pt_session' => 'Sessione Personal Trainer',
            default => 'Altro'
        };
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabel(): string
    {
        return match($this->paymentMethod) {
            'cash' => 'Contanti',
            'card' => 'Carta',
            'bank_transfer' => 'Bonifico',
            default => 'Altro'
        };
    }
}
