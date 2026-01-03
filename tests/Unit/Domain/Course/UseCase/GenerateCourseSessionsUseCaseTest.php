<?php

namespace App\Tests\Unit\Domain\Course\UseCase;

use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Repository\CourseScheduleRepositoryInterface;
use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use App\Domain\Course\UseCase\GenerateCourseSessionsUseCase;
use PHPUnit\Framework\TestCase;

class GenerateCourseSessionsUseCaseTest extends TestCase
{
    private CourseScheduleRepositoryInterface $scheduleRepository;
    private CourseSessionRepositoryInterface $sessionRepository;
    private GenerateCourseSessionsUseCase $useCase;

    protected function setUp(): void
    {
        $this->scheduleRepository = $this->createMock(CourseScheduleRepositoryInterface::class);
        $this->sessionRepository = $this->createMock(CourseSessionRepositoryInterface::class);

        $this->useCase = new GenerateCourseSessionsUseCase(
            $this->scheduleRepository,
            $this->sessionRepository
        );
    }

    public function testExecuteGeneratesSessionsForActiveSchedules(): void
    {
        $course = $this->createMock(GymCourse::class);
        $course->method('getStatus')->willReturn('active');

        $schedule = $this->createMock(CourseSchedule::class);
        $schedule->method('getCourse')->willReturn($course);
        $schedule->method('getDayOfWeek')->willReturn('monday');

        $this->scheduleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$schedule]);

        $this->sessionRepository
            ->expects($this->atLeastOnce())
            ->method('exists')
            ->willReturn(false);

        $this->sessionRepository
            ->expects($this->atLeastOnce())
            ->method('save');

        $this->sessionRepository
            ->expects($this->once())
            ->method('flush');

        $count = $this->useCase->execute(4, true);

        // Per 4 settimane dovremmo avere almeno 4 sessioni (una per lunedì)
        // Può essere 5 se includiamo la settimana corrente e il lunedì è già passato
        $this->assertGreaterThanOrEqual(4, $count);
        $this->assertLessThanOrEqual(5, $count);
    }

    public function testExecuteSkipsInactiveSchedules(): void
    {
        $course = $this->createMock(GymCourse::class);
        $course->method('getStatus')->willReturn('suspended');

        $schedule = $this->createMock(CourseSchedule::class);
        $schedule->method('getCourse')->willReturn($course);

        $this->scheduleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$schedule]);

        $this->sessionRepository
            ->expects($this->never())
            ->method('save');

        $count = $this->useCase->execute(4);

        $this->assertEquals(0, $count);
    }

    public function testExecuteDoesNotDuplicateExistingSessions(): void
    {
        $course = $this->createMock(GymCourse::class);
        $course->method('getStatus')->willReturn('active');

        $schedule = $this->createMock(CourseSchedule::class);
        $schedule->method('getCourse')->willReturn($course);
        $schedule->method('getDayOfWeek')->willReturn('tuesday');

        $this->scheduleRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$schedule]);

        // Simula che tutte le sessioni esistano già
        $this->sessionRepository
            ->method('exists')
            ->willReturn(true);

        $this->sessionRepository
            ->expects($this->never())
            ->method('save');

        $count = $this->useCase->execute(4);

        $this->assertEquals(0, $count);
    }

    public function testExecuteStartsFromMondayWhenIncludeCurrentWeekIsTrue(): void
    {
        $course = $this->createMock(GymCourse::class);
        $course->method('getStatus')->willReturn('active');

        $schedule = $this->createMock(CourseSchedule::class);
        $schedule->method('getCourse')->willReturn($course);
        $schedule->method('getDayOfWeek')->willReturn('monday');

        $this->scheduleRepository
            ->method('findAll')
            ->willReturn([$schedule]);

        $this->sessionRepository
            ->method('exists')
            ->willReturn(false);

        // Verifica che le date inizino da lunedì della settimana corrente
        $savedSessions = [];
        $this->sessionRepository
            ->method('save')
            ->willReturnCallback(function(CourseSession $session) use (&$savedSessions) {
                $savedSessions[] = $session;
            });

        $this->useCase->execute(1, true);

        $this->assertNotEmpty($savedSessions);
        $firstSession = $savedSessions[0];
        $sessionDate = $firstSession->getSessionDate();

        // La prima sessione dovrebbe essere un lunedì
        $this->assertEquals('Monday', $sessionDate->format('l'));
    }

    public function testExecuteGeneratesCorrectNumberOfWeeks(): void
    {
        $course = $this->createMock(GymCourse::class);
        $course->method('getStatus')->willReturn('active');

        $schedule = $this->createMock(CourseSchedule::class);
        $schedule->method('getCourse')->willReturn($course);
        $schedule->method('getDayOfWeek')->willReturn('wednesday');

        $this->scheduleRepository
            ->method('findAll')
            ->willReturn([$schedule]);

        $this->sessionRepository
            ->method('exists')
            ->willReturn(false);

        $this->sessionRepository
            ->expects($this->once())
            ->method('flush');

        // Genera per 8 settimane
        $count = $this->useCase->execute(8);

        // Dovrebbe creare 8 sessioni (una per mercoledì per 8 settimane)
        $this->assertEquals(8, $count);
    }
}
