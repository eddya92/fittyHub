<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103083720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course_sessions (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, schedule_id INT NOT NULL, session_date DATE NOT NULL, status VARCHAR(50) NOT NULL, max_participants INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_33D4F045591CC992 (course_id), INDEX IDX_33D4F045A40BC2D5 (schedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course_sessions ADD CONSTRAINT FK_33D4F045591CC992 FOREIGN KEY (course_id) REFERENCES gym_courses (id)');
        $this->addSql('ALTER TABLE course_sessions ADD CONSTRAINT FK_33D4F045A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES course_schedules (id)');
        $this->addSql('ALTER TABLE course_enrollments ADD session_id INT DEFAULT NULL, CHANGE schedule_id schedule_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course_enrollments ADD CONSTRAINT FK_B8B6F1E6613FECDF FOREIGN KEY (session_id) REFERENCES course_sessions (id)');
        $this->addSql('CREATE INDEX IDX_B8B6F1E6613FECDF ON course_enrollments (session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_enrollments DROP FOREIGN KEY FK_B8B6F1E6613FECDF');
        $this->addSql('ALTER TABLE course_sessions DROP FOREIGN KEY FK_33D4F045591CC992');
        $this->addSql('ALTER TABLE course_sessions DROP FOREIGN KEY FK_33D4F045A40BC2D5');
        $this->addSql('DROP TABLE course_sessions');
        $this->addSql('DROP INDEX IDX_B8B6F1E6613FECDF ON course_enrollments');
        $this->addSql('ALTER TABLE course_enrollments DROP session_id, CHANGE schedule_id schedule_id INT NOT NULL');
    }
}
