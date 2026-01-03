<?php

namespace App\Tests\Unit\Domain\Membership\Service;

use App\Domain\Course\Entity\CourseEnrollment;
use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Repository\CourseEnrollmentRepositoryInterface;
use App\Domain\Membership\Service\EnrollmentService;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class EnrollmentServiceSessionTest extends TestCase
{
    private CourseEnrollmentRepositoryInterface $enrollmentRepository;
    private EnrollmentService $service;

    protected function setUp(): void
    {
        $this->enrollmentRepository = $this->createMock(CourseEnrollmentRepositoryInterface::class);
        $this->service = new EnrollmentService($this->enrollmentRepository);
    }

    public function testEnrollUserToSessionSuccessfully(): void
    {
        $course = $this->createMock(GymCourse::class);
        $session = $this->createMock(CourseSession::class);
        $user = $this->createMock(User::class);

        $session->method('getStatus')->willReturn('scheduled');
        $session->method('isInPast')->willReturn(false);
        $session->method('hasAvailableSpots')->willReturn(true);
        $session->method('getCourse')->willReturn($course);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'session' => $session,
                'user' => $user,
                'status' => 'active'
            ])
            ->willReturn(null);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->isInstanceOf(CourseEnrollment::class),
                true
            );

        $enrollment = $this->service->enrollUserToSession($session, $user);

        $this->assertInstanceOf(CourseEnrollment::class, $enrollment);
        $this->assertEquals('active', $enrollment->getStatus());
        $this->assertEquals($session, $enrollment->getSession());
        $this->assertEquals($course, $enrollment->getCourse());
        $this->assertEquals($user, $enrollment->getUser());
    }

    public function testEnrollUserToSessionThrowsExceptionWhenSessionNotScheduled(): void
    {
        $session = $this->createMock(CourseSession::class);
        $user = $this->createMock(User::class);

        $session->method('getStatus')->willReturn('completed');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Non è possibile iscriversi a questa sessione');

        $this->service->enrollUserToSession($session, $user);
    }

    public function testEnrollUserToSessionThrowsExceptionWhenSessionInPast(): void
    {
        $session = $this->createMock(CourseSession::class);
        $user = $this->createMock(User::class);

        $session->method('getStatus')->willReturn('scheduled');
        $session->method('isInPast')->willReturn(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Non è possibile iscriversi a una sessione passata');

        $this->service->enrollUserToSession($session, $user);
    }

    public function testEnrollUserToSessionThrowsExceptionWhenNoSpots(): void
    {
        $session = $this->createMock(CourseSession::class);
        $user = $this->createMock(User::class);

        $session->method('getStatus')->willReturn('scheduled');
        $session->method('isInPast')->willReturn(false);
        $session->method('hasAvailableSpots')->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sessione al completo');

        $this->service->enrollUserToSession($session, $user);
    }

    public function testEnrollUserToSessionThrowsExceptionWhenAlreadyEnrolled(): void
    {
        $session = $this->createMock(CourseSession::class);
        $user = $this->createMock(User::class);

        $session->method('getStatus')->willReturn('scheduled');
        $session->method('isInPast')->willReturn(false);
        $session->method('hasAvailableSpots')->willReturn(true);

        $existingEnrollment = $this->createMock(CourseEnrollment::class);

        $this->enrollmentRepository
            ->method('findOneBy')
            ->willReturn($existingEnrollment);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Utente già iscritto a questa sessione');

        $this->service->enrollUserToSession($session, $user);
    }

    public function testEnrollUserToSessionForFutureSession(): void
    {
        $course = $this->createMock(GymCourse::class);
        $session = $this->createMock(CourseSession::class);
        $user = $this->createMock(User::class);

        // Sessione futura
        $session->method('getStatus')->willReturn('scheduled');
        $session->method('isInPast')->willReturn(false);
        $session->method('hasAvailableSpots')->willReturn(true);
        $session->method('getCourse')->willReturn($course);

        $this->enrollmentRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('save');

        $enrollment = $this->service->enrollUserToSession($session, $user);

        $this->assertNotNull($enrollment);
        $this->assertEquals('active', $enrollment->getStatus());
    }
}
