<?php

namespace App\Domain\Payment\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\User\Entity\User;

class GetPaymentHistoryUseCase
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {
    }

    /**
     * Get payment history with optional filters
     */
    public function execute(
        ?Gym $gym = null,
        ?User $user = null,
        ?string $paymentType = null,
        ?string $paymentMethod = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        return $this->paymentRepository->findWithFilters(
            $gym,
            $user,
            $paymentType,
            $paymentMethod,
            $startDate,
            $endDate
        );
    }

    /**
     * Get total revenue for a gym
     */
    public function getTotalRevenue(
        Gym $gym,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): float {
        return $this->paymentRepository->getTotalRevenue($gym, $startDate, $endDate);
    }

    /**
     * Get revenue grouped by payment type
     */
    public function getRevenueByType(
        Gym $gym,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        return $this->paymentRepository->getRevenueByType($gym, $startDate, $endDate);
    }
}
