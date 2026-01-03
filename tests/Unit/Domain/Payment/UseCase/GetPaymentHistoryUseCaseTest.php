<?php

namespace App\Tests\Unit\Domain\Payment\UseCase;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\Payment\UseCase\GetPaymentHistoryUseCase;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class GetPaymentHistoryUseCaseTest extends TestCase
{
    private PaymentRepositoryInterface $paymentRepository;
    private GetPaymentHistoryUseCase $useCase;

    protected function setUp(): void
    {
        $this->paymentRepository = $this->createMock(PaymentRepositoryInterface::class);
        $this->useCase = new GetPaymentHistoryUseCase($this->paymentRepository);
    }

    public function testExecuteReturnsPaymentsWithNoFilters(): void
    {
        $payment1 = $this->createMock(Payment::class);
        $payment2 = $this->createMock(Payment::class);
        $expectedPayments = [$payment1, $payment2];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findWithFilters')
            ->with(null, null, null, null, null, null)
            ->willReturn($expectedPayments);

        $result = $this->useCase->execute();

        $this->assertSame($expectedPayments, $result);
    }

    public function testExecuteReturnsPaymentsFilteredByGym(): void
    {
        $gym = $this->createMock(Gym::class);
        $payment1 = $this->createMock(Payment::class);
        $expectedPayments = [$payment1];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findWithFilters')
            ->with($gym, null, null, null, null, null)
            ->willReturn($expectedPayments);

        $result = $this->useCase->execute(gym: $gym);

        $this->assertSame($expectedPayments, $result);
    }

    public function testExecuteReturnsPaymentsFilteredByUser(): void
    {
        $user = $this->createMock(User::class);
        $payment1 = $this->createMock(Payment::class);
        $expectedPayments = [$payment1];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findWithFilters')
            ->with(null, $user, null, null, null, null)
            ->willReturn($expectedPayments);

        $result = $this->useCase->execute(user: $user);

        $this->assertSame($expectedPayments, $result);
    }

    public function testExecuteReturnsPaymentsFilteredByPaymentType(): void
    {
        $payment1 = $this->createMock(Payment::class);
        $expectedPayments = [$payment1];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findWithFilters')
            ->with(null, null, 'membership', null, null, null)
            ->willReturn($expectedPayments);

        $result = $this->useCase->execute(paymentType: 'membership');

        $this->assertSame($expectedPayments, $result);
    }

    public function testExecuteReturnsPaymentsFilteredByPaymentMethod(): void
    {
        $payment1 = $this->createMock(Payment::class);
        $expectedPayments = [$payment1];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findWithFilters')
            ->with(null, null, null, 'cash', null, null)
            ->willReturn($expectedPayments);

        $result = $this->useCase->execute(paymentMethod: 'cash');

        $this->assertSame($expectedPayments, $result);
    }

    public function testExecuteReturnsPaymentsFilteredByDateRange(): void
    {
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $payment1 = $this->createMock(Payment::class);
        $expectedPayments = [$payment1];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findWithFilters')
            ->with(null, null, null, null, $startDate, $endDate)
            ->willReturn($expectedPayments);

        $result = $this->useCase->execute(
            startDate: $startDate,
            endDate: $endDate
        );

        $this->assertSame($expectedPayments, $result);
    }

    public function testExecuteReturnsPaymentsWithAllFilters(): void
    {
        $gym = $this->createMock(Gym::class);
        $user = $this->createMock(User::class);
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $payment1 = $this->createMock(Payment::class);
        $expectedPayments = [$payment1];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findWithFilters')
            ->with($gym, $user, 'course_enrollment', 'card', $startDate, $endDate)
            ->willReturn($expectedPayments);

        $result = $this->useCase->execute(
            $gym,
            $user,
            'course_enrollment',
            'card',
            $startDate,
            $endDate
        );

        $this->assertSame($expectedPayments, $result);
    }

    public function testGetTotalRevenueReturnsCorrectAmount(): void
    {
        $gym = $this->createMock(Gym::class);
        $expectedRevenue = 1500.50;

        $this->paymentRepository
            ->expects($this->once())
            ->method('getTotalRevenue')
            ->with($gym, null, null)
            ->willReturn($expectedRevenue);

        $result = $this->useCase->getTotalRevenue($gym);

        $this->assertEquals($expectedRevenue, $result);
    }

    public function testGetTotalRevenueWithDateRange(): void
    {
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $expectedRevenue = 2500.75;

        $this->paymentRepository
            ->expects($this->once())
            ->method('getTotalRevenue')
            ->with($gym, $startDate, $endDate)
            ->willReturn($expectedRevenue);

        $result = $this->useCase->getTotalRevenue($gym, $startDate, $endDate);

        $this->assertEquals($expectedRevenue, $result);
    }

    public function testGetRevenueByTypeReturnsCorrectData(): void
    {
        $gym = $this->createMock(Gym::class);
        $expectedData = [
            ['paymentType' => 'membership', 'total' => 1000, 'count' => 10],
            ['paymentType' => 'course_enrollment', 'total' => 500, 'count' => 20],
        ];

        $this->paymentRepository
            ->expects($this->once())
            ->method('getRevenueByType')
            ->with($gym, null, null)
            ->willReturn($expectedData);

        $result = $this->useCase->getRevenueByType($gym);

        $this->assertSame($expectedData, $result);
    }

    public function testGetRevenueByTypeWithDateRange(): void
    {
        $gym = $this->createMock(Gym::class);
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $expectedData = [
            ['paymentType' => 'membership', 'total' => 1500, 'count' => 15],
        ];

        $this->paymentRepository
            ->expects($this->once())
            ->method('getRevenueByType')
            ->with($gym, $startDate, $endDate)
            ->willReturn($expectedData);

        $result = $this->useCase->getRevenueByType($gym, $startDate, $endDate);

        $this->assertSame($expectedData, $result);
    }
}
