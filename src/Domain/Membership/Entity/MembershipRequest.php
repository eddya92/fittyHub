<?php

namespace App\Domain\Membership\Entity;

use App\Domain\Gym\Entity\Gym;
use App\Domain\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'membership_requests')]
#[ORM\HasLifecycleCallbacks]
class MembershipRequest
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

    #[ORM\Column(length: 20)]
    private string $status = 'pending'; // pending, approved, rejected

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null; // Messaggio dell'utente

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adminNotes = null; // Note dell'admin

    #[ORM\Column]
    private ?\DateTimeImmutable $requestedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\ManyToOne]
    private ?User $respondedBy = null; // Admin che ha approvato/rifiutato

    public function __construct()
    {
        $this->requestedAt = new \DateTimeImmutable();
        $this->status = 'pending';
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getAdminNotes(): ?string
    {
        return $this->adminNotes;
    }

    public function setAdminNotes(?string $adminNotes): static
    {
        $this->adminNotes = $adminNotes;
        return $this;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): static
    {
        $this->requestedAt = $requestedAt;
        return $this;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?\DateTimeImmutable $respondedAt): static
    {
        $this->respondedAt = $respondedAt;
        return $this;
    }

    public function getRespondedBy(): ?User
    {
        return $this->respondedBy;
    }

    public function setRespondedBy(?User $respondedBy): static
    {
        $this->respondedBy = $respondedBy;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function approve(User $admin): void
    {
        $this->status = 'approved';
        $this->respondedAt = new \DateTimeImmutable();
        $this->respondedBy = $admin;
    }

    public function reject(User $admin, ?string $notes = null): void
    {
        $this->status = 'rejected';
        $this->respondedAt = new \DateTimeImmutable();
        $this->respondedBy = $admin;
        if ($notes) {
            $this->adminNotes = $notes;
        }
    }
}
