<?php

namespace App\Tests\Unit\Domain\Gym\Service;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Gym\Repository\GymAttendanceRepositoryInterface;
use App\Domain\Gym\Service\CheckInService;
use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\Membership\Repository\MembershipRepositoryInterface;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class CheckInServiceTest extends TestCase
{
    private GymAttendanceRepositoryInterface $attendanceRepository;
    private MembershipRepositoryInterface $membershipRepository;
    private MedicalCertificateRepositoryInterface $certificateRepository;
    private CheckInService $service;

    protected function setUp(): void
    {
        $this->attendanceRepository = $this->createMock(GymAttendanceRepositoryInterface::class);
        $this->membershipRepository = $this->createMock(MembershipRepositoryInterface::class);
        $this->certificateRepository = $this->createMock(MedicalCertificateRepositoryInterface::class);

        $this->service = new CheckInService(
            $this->attendanceRepository,
            $this->membershipRepository,
            $this->certificateRepository
        );
    }

    public function testCanCheckInReturnsFalseWhenNoActiveMembership(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $this->membershipRepository
            ->expects($this->once())
            ->method('findActiveByGym')
            ->with($gym, $user)
            ->willReturn(null);

        $result = $this->service->canCheckIn($user, $gym);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('Nessun abbonamento attivo trovato.', $result['reason']);
    }

    public function testCanCheckInReturnsFalseWhenMembershipExpired(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $membership = $this->createMock(GymMembership::class);
        $expiredDate = new \DateTime('-1 day');
        $membership->method('getEndDate')->willReturn($expiredDate);

        $this->membershipRepository
            ->method('findActiveByGym')
            ->willReturn($membership);

        $result = $this->service->canCheckIn($user, $gym);

        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('Abbonamento scaduto', $result['reason']);
    }

    public function testCanCheckInReturnsFalseWhenNoCertificate(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $membership = $this->createMock(GymMembership::class);
        $futureDate = new \DateTime('+1 month');
        $membership->method('getEndDate')->willReturn($futureDate);

        $this->membershipRepository
            ->method('findActiveByGym')
            ->willReturn($membership);

        $this->certificateRepository
            ->expects($this->once())
            ->method('findValidCertificateForUserAndGym')
            ->with($user, $gym)
            ->willReturn(null);

        $result = $this->service->canCheckIn($user, $gym);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('Certificato medico mancante o scaduto.', $result['reason']);
    }

    public function testCanCheckInReturnsTrueWhenAllConditionsMet(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $membership = $this->createMock(GymMembership::class);
        $futureDate = new \DateTime('+1 month');
        $membership->method('getEndDate')->willReturn($futureDate);

        $this->membershipRepository
            ->method('findActiveByGym')
            ->willReturn($membership);

        $certificate = $this->createMock(MedicalCertificate::class);

        $this->certificateRepository
            ->method('findValidCertificateForUserAndGym')
            ->willReturn($certificate);

        $result = $this->service->canCheckIn($user, $gym);

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['reason']);
        $this->assertSame($membership, $result['membership']);
    }

    public function testCheckInThrowsExceptionWhenCannotCheckIn(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $this->membershipRepository
            ->method('findActiveByGym')
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Nessun abbonamento attivo trovato.');

        $this->service->checkIn($user, $gym);
    }

    public function testCheckInCreatesAttendanceWhenAllowed(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $membership = $this->createMock(GymMembership::class);
        $futureDate = new \DateTime('+1 month');
        $membership->method('getEndDate')->willReturn($futureDate);

        $this->membershipRepository
            ->method('findActiveByGym')
            ->willReturn($membership);

        $certificate = $this->createMock(MedicalCertificate::class);

        $this->certificateRepository
            ->method('findValidCertificateForUserAndGym')
            ->willReturn($certificate);

        $this->attendanceRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->isInstanceOf(GymAttendance::class),
                true
            );

        $attendance = $this->service->checkIn($user, $gym);

        $this->assertInstanceOf(GymAttendance::class, $attendance);
        $this->assertEquals('gym_entrance', $attendance->getType());
    }

    public function testCheckInWithCustomType(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $membership = $this->createMock(GymMembership::class);
        $futureDate = new \DateTime('+1 month');
        $membership->method('getEndDate')->willReturn($futureDate);

        $this->membershipRepository
            ->method('findActiveByGym')
            ->willReturn($membership);

        $certificate = $this->createMock(MedicalCertificate::class);

        $this->certificateRepository
            ->method('findValidCertificateForUserAndGym')
            ->willReturn($certificate);

        $this->attendanceRepository
            ->method('save');

        $attendance = $this->service->checkIn($user, $gym, 'course_session');

        $this->assertEquals('course_session', $attendance->getType());
    }

    public function testGetUserAttendanceHistory(): void
    {
        $user = $this->createMock(User::class);
        $gym = $this->createMock(Gym::class);

        $expectedAttendances = [
            $this->createMock(GymAttendance::class),
            $this->createMock(GymAttendance::class)
        ];

        $this->attendanceRepository
            ->expects($this->once())
            ->method('findByUserAndGym')
            ->with($user, $gym, 10)
            ->willReturn($expectedAttendances);

        $result = $this->service->getUserAttendanceHistory($user, $gym);

        $this->assertCount(2, $result);
        $this->assertSame($expectedAttendances, $result);
    }

    public function testGetAttendanceStatsWithoutDateRange(): void
    {
        $gym = $this->createMock(Gym::class);

        $this->attendanceRepository
            ->expects($this->once())
            ->method('countByGymAndDateRange')
            ->with($gym, null, null)
            ->willReturn(100);

        $this->attendanceRepository
            ->expects($this->once())
            ->method('countUniqueUsersByGymAndDateRange')
            ->with($gym, null, null)
            ->willReturn(25);

        $result = $this->service->getAttendanceStats($gym);

        $this->assertEquals(100, $result['total_check_ins']);
        $this->assertEquals(25, $result['unique_users']);
    }

    public function testGetAttendanceStatsWithDateRange(): void
    {
        $gym = $this->createMock(Gym::class);
        $from = new \DateTime('-1 week');
        $to = new \DateTime('now');

        $this->attendanceRepository
            ->expects($this->once())
            ->method('countByGymAndDateRange')
            ->with($gym, $from, $to)
            ->willReturn(50);

        $this->attendanceRepository
            ->expects($this->once())
            ->method('countUniqueUsersByGymAndDateRange')
            ->with($gym, $from, $to)
            ->willReturn(15);

        $result = $this->service->getAttendanceStats($gym, $from, $to);

        $this->assertEquals(50, $result['total_check_ins']);
        $this->assertEquals(15, $result['unique_users']);
    }
}
