<?php

namespace App\Tests\Domain\Course;

use App\Domain\Course\Entity\CourseCategory;
use App\Domain\Gym\Entity\Gym;
use PHPUnit\Framework\TestCase;

class CourseCategoryTest extends TestCase
{
    public function testCreateCourseCategory(): void
    {
        $gym = new Gym();
        $category = new CourseCategory();

        $category->setName('Yoga');
        $category->setColor('#FF5733');
        $category->setGym($gym);

        $this->assertEquals('Yoga', $category->getName());
        $this->assertEquals('#FF5733', $category->getColor());
        $this->assertSame($gym, $category->getGym());
        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getCreatedAt());
    }

    public function testCategoryHasValidColorFormat(): void
    {
        $category = new CourseCategory();
        $category->setColor('#3B82F6');

        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $category->getColor());
    }

    public function testCategoryIdIsNullBeforePersistence(): void
    {
        $category = new CourseCategory();

        $this->assertNull($category->getId());
    }
}
