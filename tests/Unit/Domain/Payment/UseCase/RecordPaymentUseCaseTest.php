<?php

namespace App\Tests\Unit\Domain\Payment\UseCase;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\Payment\UseCase\RecordPaymentUseCase;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class RecordPaymentUseCaseTest extends TestCase
{
    private PaymentRepositoryInterface $paymentRepository;
    private RecordPaymentUseCase $useCase;

    protected function setUp(): void
    {
        $this->paymentRepository = $this->createMock(PaymentRepositoryInterface::class);
        $this->useCase = new RecordPaymentUseCase($this->paymentRepository);
    }

    public function testExecuteCreatesPaymentSuccessfully(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $this->paymentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Payment::class), true);

        $payment = $this->useCase->execute(
            $user,
            $gym,
            '100.00',
            'cash',
            'other',
            $paymentDate
        );

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($user, $payment->getUser());
        $this->assertSame($gym, $payment->getGym());
        $this->assertEquals('100.00', $payment->getAmount());
        $this->assertEquals('cash', $payment->getPaymentMethod());
        $this->assertEquals('other', $payment->getPaymentType());
        $this->assertSame($paymentDate, $payment->getPaymentDate());
    }

    public function testExecuteThrowsExceptionForNegativeAmount(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('L\'importo deve essere maggiore di zero');

        $this->useCase->execute(
            $user,
            $gym,
            '-50.00',
            'cash',
            'other',
            $paymentDate
        );
    }

    public function testExecuteThrowsExceptionForZeroAmount(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('L\'importo deve essere maggiore di zero');

        $this->useCase->execute(
            $user,
            $gym,
            '0',
            'cash',
            'other',
            $paymentDate
        );
    }

    public function testExecuteThrowsExceptionForInvalidPaymentMethod(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Metodo di pagamento non valido');

        $this->useCase->execute(
            $user,
            $gym,
            '100.00',
            'bitcoin', // Invalid payment method
            'other',
            $paymentDate
        );
    }

    public function testExecuteThrowsExceptionForInvalidPaymentType(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tipo di pagamento non valido');

        $this->useCase->execute(
            $user,
            $gym,
            '100.00',
            'cash',
            'invalid_type', // Invalid payment type
            $paymentDate
        );
    }

    public function testExecuteThrowsExceptionWhenMembershipTypeButNoMembership(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Per pagamenti abbonamento è necessario specificare l\'abbonamento');

        $this->useCase->execute(
            $user,
            $gym,
            '100.00',
            'cash',
            'membership', // Requires membership
            $paymentDate,
            null, // createdBy
            null  // No membership provided
        );
    }

    public function testExecuteThrowsExceptionWhenCourseEnrollmentTypeButNoEnrollment(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Per pagamenti iscrizione corso è necessario specificare l\'iscrizione');

        $this->useCase->execute(
            $user,
            $gym,
            '100.00',
            'cash',
            'course_enrollment', // Requires enrollment
            $paymentDate,
            null, // createdBy
            null, // membership
            null  // No enrollment provided
        );
    }

    public function testExecuteThrowsExceptionWhenMembershipBelongsToWrongUser(): void
    {
        $user = $this->createMock(User::class);
        $otherUser = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $membership = $this->createMock(GymMembership::class);
        $membership->method('getUser')->willReturn($otherUser);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('L\'abbonamento non appartiene all\'utente specificato');

        $this->useCase->execute(
            $user,
            $gym,
            '100.00',
            'cash',
            'membership',
            $paymentDate,
            null,
            $membership
        );
    }

    public function testExecuteThrowsExceptionWhenEnrollmentBelongsToWrongUser(): void
    {
        $user = $this->createMock(User::class);
        $otherUser = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $enrollment = $this->createMock(CourseEnrollment::class);
        $enrollment->method('getUser')->willReturn($otherUser);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('L\'iscrizione al corso non appartiene all\'utente specificato');

        $this->useCase->execute(
            $user,
            $gym,
            '100.00',
            'cash',
            'course_enrollment',
            $paymentDate,
            null,
            null,
            $enrollment
        );
    }

    public function testExecuteCreatesPaymentWithMembership(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();
        $createdBy = $this->createMock(User::class);

        $membership = $this->createMock(GymMembership::class);
        $membership->method('getUser')->willReturn($user);

        $this->paymentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Payment::class), true);

        $payment = $this->useCase->execute(
            $user,
            $gym,
            '150.00',
            'card',
            'membership',
            $paymentDate,
            $createdBy,
            $membership,
            null,
            'Abbonamento mensile',
            'RIC-001'
        );

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($membership, $payment->getMembership());
        $this->assertSame($createdBy, $payment->getCreatedBy());
        $this->assertEquals('Abbonamento mensile', $payment->getNotes());
        $this->assertEquals('RIC-001', $payment->getTransactionReference());
    }

    public function testExecuteCreatesPaymentWithCourseEnrollment(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $enrollment = $this->createMock(CourseEnrollment::class);
        $enrollment->method('getUser')->willReturn($user);

        $this->paymentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Payment::class), true);

        $payment = $this->useCase->execute(
            $user,
            $gym,
            '50.00',
            'bank_transfer',
            'course_enrollment',
            $paymentDate,
            null,
            null,
            $enrollment,
            'Iscrizione corso yoga'
        );

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($enrollment, $payment->getCourseEnrollment());
        $this->assertEquals('Iscrizione corso yoga', $payment->getNotes());
    }

    public function testExecuteAcceptsAllValidPaymentMethods(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $validMethods = ['cash', 'card', 'bank_transfer', 'other'];

        foreach ($validMethods as $method) {
            $this->paymentRepository
                ->expects($this->once())
                ->method('save')
                ->with($this->isInstanceOf(Payment::class), true);

            $payment = $this->useCase->execute(
                $user,
                $gym,
                '100.00',
                $method,
                'other',
                $paymentDate
            );

            $this->assertEquals($method, $payment->getPaymentMethod());

            // Reset mock for next iteration
            $this->setUp();
        }
    }

    public function testExecuteAcceptsAllValidPaymentTypes(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);
        $paymentDate = new \DateTime();

        $validTypes = ['other', 'pt_session']; // membership and course_enrollment tested separately

        foreach ($validTypes as $type) {
            $this->paymentRepository
                ->expects($this->once())
                ->method('save')
                ->with($this->isInstanceOf(Payment::class), true);

            $payment = $this->useCase->execute(
                $user,
                $gym,
                '100.00',
                'cash',
                $type,
                $paymentDate
            );

            $this->assertEquals($type, $payment->getPaymentType());

            // Reset mock for next iteration
            $this->setUp();
        }
    }
}
