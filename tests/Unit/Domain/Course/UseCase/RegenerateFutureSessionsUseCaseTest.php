<?php

namespace App\Tests\Unit\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;
use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use App\Domain\Course\UseCase\GenerateCourseSessionsUseCase;
use App\Domain\Course\UseCase\RegenerateFutureSessionsUseCase;
use PHPUnit\Framework\TestCase;

class RegenerateFutureSessionsUseCaseTest extends TestCase
{
    private CourseSessionRepositoryInterface $sessionRepository;
    private CourseScheduleRepositoryInterface $scheduleRepository;
    private GenerateCourseSessionsUseCase $generateSessions;
    private RegenerateFutureSessionsUseCase $useCase;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(CourseSessionRepositoryInterface::class);
        $this->scheduleRepository = $this->createMock(CourseScheduleRepositoryInterface::class);
        $this->generateSessions = $this->createMock(GenerateCourseSessionsUseCase::class);

        $this->useCase = new RegenerateFutureSessionsUseCase(
            $this->sessionRepository,
            $this->scheduleRepository,
            $this->generateSessions
        );
    }

    public function testExecuteDeletesScheduledSessions(): void
    {
        $scheduledSession = $this->createMock(CourseSession::class);
        $scheduledSession->method('getStatus')->willReturn('scheduled');

        $this->sessionRepository
            ->expects($this->once())
            ->method('findBetweenDates')
            ->willReturn([$scheduledSession]);

        $this->sessionRepository
            ->expects($this->once())
            ->method('remove')
            ->with($scheduledSession);

        $this->sessionRepository
            ->expects($this->once())
            ->method('flush');

        $this->generateSessions
            ->expects($this->once())
            ->method('execute')
            ->with(5, true)
            ->willReturn(10);

        $result = $this->useCase->execute(5);

        $this->assertEquals(1, $result['deleted']);
        $this->assertEquals(10, $result['created']);
    }

    public function testExecuteDoesNotDeleteCompletedSessions(): void
    {
        $completedSession = $this->createMock(CourseSession::class);
        $completedSession->method('getStatus')->willReturn('completed');

        $this->sessionRepository
            ->method('findBetweenDates')
            ->willReturn([$completedSession]);

        $this->sessionRepository
            ->expects($this->never())
            ->method('remove');

        $this->generateSessions
            ->method('execute')
            ->willReturn(0);

        $result = $this->useCase->execute(5);

        $this->assertEquals(0, $result['deleted']);
    }

    public function testExecuteDoesNotDeleteCancelledSessions(): void
    {
        $cancelledSession = $this->createMock(CourseSession::class);
        $cancelledSession->method('getStatus')->willReturn('cancelled');

        $this->sessionRepository
            ->method('findBetweenDates')
            ->willReturn([$cancelledSession]);

        $this->sessionRepository
            ->expects($this->never())
            ->method('remove');

        $this->generateSessions
            ->method('execute')
            ->willReturn(0);

        $result = $this->useCase->execute(5);

        $this->assertEquals(0, $result['deleted']);
    }

    public function testRegenerateCurrentAndNextMonth(): void
    {
        $scheduledSession1 = $this->createMock(CourseSession::class);
        $scheduledSession1->method('getStatus')->willReturn('scheduled');

        $scheduledSession2 = $this->createMock(CourseSession::class);
        $scheduledSession2->method('getStatus')->willReturn('scheduled');

        $this->sessionRepository
            ->expects($this->once())
            ->method('findBetweenDates')
            ->willReturn([$scheduledSession1, $scheduledSession2]);

        $this->sessionRepository
            ->expects($this->exactly(2))
            ->method('remove');

        $this->sessionRepository
            ->expects($this->once())
            ->method('flush');

        $this->generateSessions
            ->expects($this->once())
            ->method('execute')
            ->with(8, true)
            ->willReturn(20);

        $result = $this->useCase->regenerateCurrentAndNextMonth();

        $this->assertEquals(2, $result['deleted']);
        $this->assertEquals(20, $result['created']);
    }

    public function testExecuteWithDeleteFromTodayTrue(): void
    {
        $this->sessionRepository
            ->method('findBetweenDates')
            ->willReturn([]);

        $this->generateSessions
            ->method('execute')
            ->willReturn(5);

        $result = $this->useCase->execute(4, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('deleted', $result);
        $this->assertArrayHasKey('created', $result);
    }
}
