<?php

namespace App\Tests\Domain\Gym;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymAttendance;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class GymAttendanceTest extends TestCase
{
    public function testCreateGymAttendance(): void
    {
        $user = new User();
        $gym = new Gym();
        $membership = new GymMembership();

        $attendance = new GymAttendance();
        $attendance->setUser($user);
        $attendance->setGym($gym);
        $attendance->setGymMembership($membership);
        $attendance->setType('gym_entrance');

        $this->assertSame($user, $attendance->getUser());
        $this->assertSame($gym, $attendance->getGym());
        $this->assertSame($membership, $attendance->getGymMembership());
        $this->assertEquals('gym_entrance', $attendance->getType());
        $this->assertInstanceOf(\DateTimeInterface::class, $attendance->getCheckInTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $attendance->getCreatedAt());
    }

    public function testAttendanceTypeCanBeCourse(): void
    {
        $attendance = new GymAttendance();
        $attendance->setType('course');

        $this->assertEquals('course', $attendance->getType());
    }

    public function testAttendanceDefaultTypeIsGymEntrance(): void
    {
        $attendance = new GymAttendance();

        $this->assertEquals('gym_entrance', $attendance->getType());
    }

    public function testCheckInTimeIsSetOnConstruction(): void
    {
        $before = new \DateTime();
        $attendance = new GymAttendance();
        $after = new \DateTime();

        $checkInTime = $attendance->getCheckInTime();

        $this->assertInstanceOf(\DateTimeInterface::class, $checkInTime);
        $this->assertGreaterThanOrEqual($before, $checkInTime);
        $this->assertLessThanOrEqual($after, $checkInTime);
    }

    public function testCanSetCheckOutTime(): void
    {
        $attendance = new GymAttendance();
        $checkOutTime = new \DateTime('+1 hour');

        $attendance->setCheckOutTime($checkOutTime);

        $this->assertSame($checkOutTime, $attendance->getCheckOutTime());
    }

    public function testCanSetNotes(): void
    {
        $attendance = new GymAttendance();
        $notes = 'Utente ha dimenticato la tessera';

        $attendance->setNotes($notes);

        $this->assertEquals($notes, $attendance->getNotes());
    }

    public function testCheckOutCalculatesDuration(): void
    {
        $attendance = new GymAttendance();
        $checkInTime = new \DateTime('10:00');
        $checkOutTime = new \DateTime('12:30');

        $attendance->setCheckInTime($checkInTime);
        $attendance->setCheckOutTime($checkOutTime);

        // 2 hours 30 minutes = 150 minutes
        $this->assertEquals(150, $attendance->getDuration());
    }
}
