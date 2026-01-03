<?php

namespace App\Tests\Unit\Domain\Course\Service;

use App\Domain\Course\Entity\CourseSession;
use App\Domain\Course\Entity\GymCourse;
use App\Domain\Course\Entity\CourseSchedule;
use App\Domain\Course\Repository\CourseRepositoryInterface;
use App\Domain\Course\Repository\CourseCategoryRepositoryInterface;
use App\Domain\Course\Repository\CourseSessionRepositoryInterface;
use App\Domain\Course\Service\CourseCalendarService;
use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Entity\GymSettings;
use App\Domain\Gym\Repository\GymSettingsRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CourseCalendarServiceTest extends TestCase
{
    private CourseRepositoryInterface $courseRepository;
    private CourseCategoryRepositoryInterface $categoryRepository;
    private GymSettingsRepositoryInterface $settingsRepository;
    private CourseSessionRepositoryInterface $sessionRepository;
    private CourseCalendarService $service;

    protected function setUp(): void
    {
        $this->courseRepository = $this->createMock(CourseRepositoryInterface::class);
        $this->categoryRepository = $this->createMock(CourseCategoryRepositoryInterface::class);
        $this->settingsRepository = $this->createMock(GymSettingsRepositoryInterface::class);
        $this->sessionRepository = $this->createMock(CourseSessionRepositoryInterface::class);

        $this->service = new CourseCalendarService(
            $this->courseRepository,
            $this->categoryRepository,
            $this->settingsRepository,
            $this->sessionRepository
        );
    }

    public function testGetWeeklyCalendarWithSessionsReturnsCorrectStructure(): void
    {
        $weekStart = new \DateTime('2026-01-05'); // Lunedì

        $course = $this->createMock(GymCourse::class);
        $schedule = $this->createMock(CourseSchedule::class);

        $session = $this->createMock(CourseSession::class);
        $session->method('getSessionDate')->willReturn($weekStart);
        $session->method('getCourse')->willReturn($course);
        $session->method('getSchedule')->willReturn($schedule);

        $schedule->method('getStartTime')->willReturn(new \DateTime('09:00'));

        $this->sessionRepository
            ->expects($this->once())
            ->method('findBetweenDates')
            ->willReturn([$session]);

        $calendar = $this->service->getWeeklyCalendarWithSessions($weekStart);

        $this->assertIsArray($calendar);
        $this->assertArrayHasKey('monday', $calendar);
        $this->assertArrayHasKey('tuesday', $calendar);
        $this->assertCount(1, $calendar['monday']);
        $this->assertSame($session, $calendar['monday'][0]);
    }

    public function testGetWeeklyCalendarWithSessionsOrganizesByDay(): void
    {
        $weekStart = new \DateTime('2026-01-05'); // Lunedì

        $mondaySession = $this->createMock(CourseSession::class);
        $mondaySession->method('getSessionDate')->willReturn(new \DateTime('2026-01-05'));
        $mondaySession->method('getSchedule')->willReturn($this->createScheduleWithTime('09:00'));

        $tuesdaySession = $this->createMock(CourseSession::class);
        $tuesdaySession->method('getSessionDate')->willReturn(new \DateTime('2026-01-06'));
        $tuesdaySession->method('getSchedule')->willReturn($this->createScheduleWithTime('10:00'));

        $this->sessionRepository
            ->method('findBetweenDates')
            ->willReturn([$mondaySession, $tuesdaySession]);

        $calendar = $this->service->getWeeklyCalendarWithSessions($weekStart);

        $this->assertCount(1, $calendar['monday']);
        $this->assertCount(1, $calendar['tuesday']);
        $this->assertCount(0, $calendar['wednesday']);
    }

    public function testGetWeeklyCalendarWithSessionsSortsByTime(): void
    {
        $weekStart = new \DateTime('2026-01-05');

        $session1 = $this->createMock(CourseSession::class);
        $session1->method('getSessionDate')->willReturn($weekStart);
        $session1->method('getSchedule')->willReturn($this->createScheduleWithTime('14:00'));

        $session2 = $this->createMock(CourseSession::class);
        $session2->method('getSessionDate')->willReturn($weekStart);
        $session2->method('getSchedule')->willReturn($this->createScheduleWithTime('09:00'));

        $this->sessionRepository
            ->method('findBetweenDates')
            ->willReturn([$session1, $session2]);

        $calendar = $this->service->getWeeklyCalendarWithSessions($weekStart);

        $this->assertCount(2, $calendar['monday']);
        // Dovrebbe essere ordinato per orario: 09:00 prima di 14:00
        $this->assertEquals('09:00', $calendar['monday'][0]->getSchedule()->getStartTime()->format('H:i'));
        $this->assertEquals('14:00', $calendar['monday'][1]->getSchedule()->getStartTime()->format('H:i'));
    }

    public function testGetCurrentWeekInfo(): void
    {
        $info = $this->service->getCurrentWeekInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('start', $info);
        $this->assertArrayHasKey('end', $info);
        $this->assertArrayHasKey('label', $info);

        $this->assertInstanceOf(\DateTime::class, $info['start']);
        $this->assertInstanceOf(\DateTime::class, $info['end']);
        $this->assertIsString($info['label']);
    }

    public function testGetWeekInfo(): void
    {
        $date = new \DateTime('2026-01-15'); // Giovedì

        $info = $this->service->getWeekInfo($date);

        $this->assertArrayHasKey('start', $info);
        $this->assertArrayHasKey('end', $info);
        $this->assertArrayHasKey('label', $info);

        // Il lunedì della settimana del 15 gennaio è il 12
        $this->assertEquals('2026-01-12', $info['start']->format('Y-m-d'));
        $this->assertEquals('2026-01-18', $info['end']->format('Y-m-d'));
    }

    public function testGetPreviousWeek(): void
    {
        $currentWeek = new \DateTime('2026-01-12'); // Lunedì
        $previousWeek = $this->service->getPreviousWeek($currentWeek);

        $this->assertEquals('2026-01-05', $previousWeek->format('Y-m-d'));
    }

    public function testGetNextWeek(): void
    {
        $currentWeek = new \DateTime('2026-01-12'); // Lunedì
        $nextWeek = $this->service->getNextWeek($currentWeek);

        $this->assertEquals('2026-01-19', $nextWeek->format('Y-m-d'));
    }

    public function testGetWeeklyCalendarWithSessionsNormalizesToMonday(): void
    {
        $thursday = new \DateTime('2026-01-08'); // Giovedì

        $this->sessionRepository
            ->expects($this->once())
            ->method('findBetweenDates')
            ->with(
                $this->callback(function(\DateTime $start) {
                    return $start->format('Y-m-d') === '2026-01-05'; // Lunedì
                }),
                $this->anything()
            )
            ->willReturn([]);

        $this->service->getWeeklyCalendarWithSessions($thursday);
    }

    private function createScheduleWithTime(string $time): CourseSchedule
    {
        $schedule = $this->createMock(CourseSchedule::class);
        $schedule->method('getStartTime')->willReturn(new \DateTime($time));
        return $schedule;
    }
}
