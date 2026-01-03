<?php

namespace App\Tests\Unit\Domain\Course\Entity;

use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseEnrollment;
use PHPUnit\Framework\TestCase;

class CourseSessionTest extends TestCase
{
    private CourseSession $session;
    private GymCourse $course;
    private CourseSchedule $schedule;

    protected function setUp(): void
    {
        $this->course = $this->createMock(GymCourse::class);
        $this->course->method('getMaxParticipants')->willReturn(20);

        $this->schedule = $this->createMock(CourseSchedule::class);
        $startTime = new \DateTime('09:00');
        $this->schedule->method('getStartTime')->willReturn($startTime);

        $this->session = new CourseSession();
        $this->session->setCourse($this->course);
        $this->session->setSchedule($this->schedule);
        $this->session->setSessionDate(new \DateTime('2026-01-13')); // Lunedì
        $this->session->setStatus('scheduled');
    }

    public function testGetMaxParticipantsInheritsFromCourse(): void
    {
        $this->assertEquals(20, $this->session->getMaxParticipants());
    }

    public function testGetMaxParticipantsUsesCustomValue(): void
    {
        $this->session->setMaxParticipants(30);
        $this->assertEquals(30, $this->session->getMaxParticipants());
    }

    public function testHasAvailableSpotsWhenEmpty(): void
    {
        $this->assertTrue($this->session->hasAvailableSpots());
    }

    public function testHasAvailableSpotsWhenFull(): void
    {
        $this->session->setMaxParticipants(2);

        // Aggiungi 2 iscritti attivi
        for ($i = 0; $i < 2; $i++) {
            $enrollment = $this->createMock(CourseEnrollment::class);
            $enrollment->method('getStatus')->willReturn('active');
            $this->session->addEnrollment($enrollment);
        }

        $this->assertFalse($this->session->hasAvailableSpots());
        $this->assertTrue($this->session->isFull());
    }

    public function testGetActiveEnrollmentsCount(): void
    {
        // Aggiungi 2 attivi e 1 cancellato
        $activeEnrollment1 = $this->createMock(CourseEnrollment::class);
        $activeEnrollment1->method('getStatus')->willReturn('active');

        $activeEnrollment2 = $this->createMock(CourseEnrollment::class);
        $activeEnrollment2->method('getStatus')->willReturn('active');

        $cancelledEnrollment = $this->createMock(CourseEnrollment::class);
        $cancelledEnrollment->method('getStatus')->willReturn('cancelled');

        $this->session->addEnrollment($activeEnrollment1);
        $this->session->addEnrollment($activeEnrollment2);
        $this->session->addEnrollment($cancelledEnrollment);

        $this->assertEquals(2, $this->session->getActiveEnrollmentsCount());
    }

    public function testGetAvailableSpots(): void
    {
        $this->session->setMaxParticipants(10);

        $enrollment = $this->createMock(CourseEnrollment::class);
        $enrollment->method('getStatus')->willReturn('active');
        $this->session->addEnrollment($enrollment);

        $this->assertEquals(9, $this->session->getAvailableSpots());
    }

    public function testGetDateTime(): void
    {
        $dateTime = $this->session->getDateTime();

        $this->assertNotNull($dateTime);
        $this->assertEquals('2026-01-13', $dateTime->format('Y-m-d'));
        $this->assertEquals('09:00', $dateTime->format('H:i'));
    }

    public function testIsInPast(): void
    {
        $pastSession = new CourseSession();
        $pastSession->setCourse($this->course);
        $pastSession->setSchedule($this->schedule);
        $pastSession->setSessionDate(new \DateTime('2020-01-01'));

        $this->assertTrue($pastSession->isInPast());
        $this->assertFalse($pastSession->isInFuture());
    }

    public function testIsInFuture(): void
    {
        $futureSession = new CourseSession();
        $futureSession->setCourse($this->course);
        $futureSession->setSchedule($this->schedule);
        $futureSession->setSessionDate(new \DateTime('+1 month'));

        $this->assertTrue($futureSession->isInFuture());
        $this->assertFalse($futureSession->isInPast());
    }

    public function testIsToday(): void
    {
        $todaySession = new CourseSession();
        $todaySession->setCourse($this->course);
        $todaySession->setSchedule($this->schedule);
        $todaySession->setSessionDate(new \DateTime('today'));

        $this->assertTrue($todaySession->isToday());
    }

    public function testDefaultStatus(): void
    {
        $newSession = new CourseSession();
        $this->assertEquals('scheduled', $newSession->getStatus());
    }

    public function testPreUpdateSetsUpdatedAt(): void
    {
        $originalUpdatedAt = $this->session->getUpdatedAt();

        sleep(1); // Assicura che il timestamp cambi
        $this->session->preUpdate();

        $this->assertGreaterThan($originalUpdatedAt, $this->session->getUpdatedAt());
    }
}
