<?php

namespace App\Domain\Medical\Entity;

use App\Domain\Medical\Repository\MedicalCertificateRepository;
use App\Domain\User\Entity\User;
use App\Domain\Membership\Entity\GymMembership;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicalCertificateRepository::class)]
class MedicalCertificate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medicalCertificates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(inversedBy: 'medicalCertificate')]
    private ?GymMembership $gymMembership = null;

    #[ORM\Column(length: 50)]
    private ?string $certificateType = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $issueDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $expiryDate = null;

    #[ORM\Column(length: 255)]
    private ?string $doctorName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $doctorNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $filePath = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'pending_review';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\ManyToOne]
    private ?User $reviewedBy = null;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
        $this->status = 'pending_review';
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

    public function getGymMembership(): ?GymMembership
    {
        return $this->gymMembership;
    }

    public function setGymMembership(?GymMembership $gymMembership): static
    {
        $this->gymMembership = $gymMembership;

        return $this;
    }

    public function getCertificateType(): ?string
    {
        return $this->certificateType;
    }

    public function setCertificateType(string $certificateType): static
    {
        $this->certificateType = $certificateType;

        return $this;
    }

    public function getIssueDate(): ?\DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(\DateTimeInterface $issueDate): static
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(\DateTimeInterface $expiryDate): static
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getDoctorName(): ?string
    {
        return $this->doctorName;
    }

    public function setDoctorName(string $doctorName): static
    {
        $this->doctorName = $doctorName;

        return $this;
    }

    public function getDoctorNumber(): ?string
    {
        return $this->doctorNumber;
    }

    public function setDoctorNumber(?string $doctorNumber): static
    {
        $this->doctorNumber = $doctorNumber;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;

        return $this;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiryDate < new \DateTime();
    }
}
