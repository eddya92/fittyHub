<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229214340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course_enrollments (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, user_id INT NOT NULL, status VARCHAR(50) NOT NULL, enrolled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', cancelled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B8B6F1E6591CC992 (course_id), INDEX IDX_B8B6F1E6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_schedules (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, day_of_week VARCHAR(20) NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B39A9C22591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gym_courses (id INT AUTO_INCREMENT NOT NULL, gym_id INT NOT NULL, instructor_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, category VARCHAR(100) NOT NULL, max_participants INT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DD79EAEBD2F03 (gym_id), INDEX IDX_DD79EAE8C4FC193 (instructor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course_enrollments ADD CONSTRAINT FK_B8B6F1E6591CC992 FOREIGN KEY (course_id) REFERENCES gym_courses (id)');
        $this->addSql('ALTER TABLE course_enrollments ADD CONSTRAINT FK_B8B6F1E6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE course_schedules ADD CONSTRAINT FK_B39A9C22591CC992 FOREIGN KEY (course_id) REFERENCES gym_courses (id)');
        $this->addSql('ALTER TABLE gym_courses ADD CONSTRAINT FK_DD79EAEBD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE gym_courses ADD CONSTRAINT FK_DD79EAE8C4FC193 FOREIGN KEY (instructor_id) REFERENCES personal_trainer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_enrollments DROP FOREIGN KEY FK_B8B6F1E6591CC992');
        $this->addSql('ALTER TABLE course_enrollments DROP FOREIGN KEY FK_B8B6F1E6A76ED395');
        $this->addSql('ALTER TABLE course_schedules DROP FOREIGN KEY FK_B39A9C22591CC992');
        $this->addSql('ALTER TABLE gym_courses DROP FOREIGN KEY FK_DD79EAEBD2F03');
        $this->addSql('ALTER TABLE gym_courses DROP FOREIGN KEY FK_DD79EAE8C4FC193');
        $this->addSql('DROP TABLE course_enrollments');
        $this->addSql('DROP TABLE course_schedules');
        $this->addSql('DROP TABLE gym_courses');
    }
}
