<?php

namespace App\Domain\Payment\Repository;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Payment\Entity\Payment;
use App\Domain\User\Entity\User;

/**
 * Repository interface for Payment
 */
interface PaymentRepositoryInterface
{
    /**
     * Find payments by user
     */
    public function findByUser(User $user): array;

    /**
     * Find payments by gym
     */
    public function findByGym(Gym $gym): array;

    /**
     * Find payments with filters
     */
    public function findWithFilters(
        ?Gym $gym = null,
        ?User $user = null,
        ?string $paymentType = null,
        ?string $paymentMethod = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array;

    /**
     * Get total revenue for a gym in a date range
     */
    public function getTotalRevenue(
        Gym $gym,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): float;

    /**
     * Get revenue grouped by payment type
     */
    public function getRevenueByType(
        Gym $gym,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array;

    /**
     * Save payment
     */
    public function save(Payment $payment, bool $flush = false): void;

    /**
     * Remove payment
     */
    public function remove(Payment $payment, bool $flush = false): void;

    /**
     * Find one payment by ID
     *
     * @return Payment|null
     */
    public function find(int $id);

    /**
     * Find all payments
     */
    public function findAll(): array;
}
