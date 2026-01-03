<?php

namespace App\Domain\Payment\UseCase;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\User\Entity\User;

class RecordPaymentUseCase
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository
    ) {
    }

    /**
     * Record a new payment
     *
     * @throws \RuntimeException if validation fails
     */
    public function execute(
        User $user,
        Gym $gym,
        string $amount,
        string $paymentMethod,
        string $paymentType,
        \DateTimeInterface $paymentDate,
        ?User $createdBy = null,
        ?GymMembership $membership = null,
        ?CourseEnrollment $courseEnrollment = null,
        ?string $notes = null,
        ?string $transactionReference = null
    ): Payment {
        // Validate amount
        if (!is_numeric($amount) || floatval($amount) <= 0) {
            throw new \RuntimeException('L\'importo deve essere maggiore di zero');
        }

        // Validate payment method
        $validMethods = ['cash', 'card', 'bank_transfer', 'other'];
        if (!in_array($paymentMethod, $validMethods)) {
            throw new \RuntimeException('Metodo di pagamento non valido');
        }

        // Validate payment type
        $validTypes = ['membership', 'course_enrollment', 'pt_session', 'other'];
        if (!in_array($paymentType, $validTypes)) {
            throw new \RuntimeException('Tipo di pagamento non valido');
        }

        // Validate related entity based on payment type
        if ($paymentType === 'membership' && !$membership) {
            throw new \RuntimeException('Per pagamenti abbonamento è necessario specificare l\'abbonamento');
        }

        if ($paymentType === 'course_enrollment' && !$courseEnrollment) {
            throw new \RuntimeException('Per pagamenti iscrizione corso è necessario specificare l\'iscrizione');
        }

        // Verify membership/enrollment belongs to the user
        if ($membership && $membership->getUser() !== $user) {
            throw new \RuntimeException('L\'abbonamento non appartiene all\'utente specificato');
        }

        if ($courseEnrollment && $courseEnrollment->getUser() !== $user) {
            throw new \RuntimeException('L\'iscrizione al corso non appartiene all\'utente specificato');
        }

        // Create payment
        $payment = new Payment();
        $payment->setUser($user);
        $payment->setGym($gym);
        $payment->setAmount($amount);
        $payment->setPaymentMethod($paymentMethod);
        $payment->setPaymentType($paymentType);
        $payment->setPaymentDate($paymentDate);
        $payment->setCreatedBy($createdBy);
        $payment->setMembership($membership);
        $payment->setCourseEnrollment($courseEnrollment);
        $payment->setNotes($notes);
        $payment->setTransactionReference($transactionReference);

        $this->paymentRepository->save($payment, true);

        return $payment;
    }
}
