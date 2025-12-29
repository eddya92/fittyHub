<?php

namespace App\Domain\Invitation\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Domain\Invitation\Repository\PTClientInvitationRepository;
use App\Domain\Invitation\State\PTClientInvitationProcessor;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PTClientInvitationRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_PT') or is_granted('ROLE_USER')"
        ),
        new GetCollection(
            security: "is_granted('ROLE_PT')"
        ),
        new Post(
            security: "is_granted('ROLE_PT')",
            processor: PTClientInvitationProcessor::class
        )
    ],
    normalizationContext: ['groups' => ['invitation:read']],
    denormalizationContext: ['groups' => ['invitation:create']]
)]
class PTClientInvitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invitation:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invitation:read', 'invitation:create'])]
    private ?PersonalTrainer $personalTrainer = null;

    #[ORM\Column(length: 180)]
    #[Groups(['invitation:read', 'invitation:create'])]
    private ?string $clientEmail = null;

    #[ORM\ManyToOne]
    #[Groups(['invitation:read'])]
    private ?User $clientUser = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $token = null;

    #[ORM\Column(length: 50)]
    #[Groups(['invitation:read'])]
    private ?string $status = 'pending';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['invitation:read', 'invitation:create'])]
    private ?string $message = null;

    #[ORM\Column]
    #[Groups(['invitation:read'])]
    private ?\DateTimeImmutable $invitedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invitation:read'])]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\Column]
    #[Groups(['invitation:read'])]
    private ?\DateTimeImmutable $expiresAt = null;

    public function __construct()
    {
        $this->invitedAt = new \DateTimeImmutable();
        $this->expiresAt = new \DateTimeImmutable('+7 days');
        $this->status = 'pending';
        $this->token = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonalTrainer(): ?PersonalTrainer
    {
        return $this->personalTrainer;
    }

    public function setPersonalTrainer(?PersonalTrainer $personalTrainer): static
    {
        $this->personalTrainer = $personalTrainer;

        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(string $clientEmail): static
    {
        $this->clientEmail = $clientEmail;

        return $this;
    }

    public function getClientUser(): ?User
    {
        return $this->clientUser;
    }

    public function setClientUser(?User $clientUser): static
    {
        $this->clientUser = $clientUser;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getInvitedAt(): ?\DateTimeImmutable
    {
        return $this->invitedAt;
    }

    public function setInvitedAt(\DateTimeImmutable $invitedAt): static
    {
        $this->invitedAt = $invitedAt;

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

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }
}
