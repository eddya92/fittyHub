<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229222308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_enrollments ADD schedule_id INT NOT NULL');
        $this->addSql('ALTER TABLE course_enrollments ADD CONSTRAINT FK_B8B6F1E6A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES course_schedules (id)');
        $this->addSql('CREATE INDEX IDX_B8B6F1E6A40BC2D5 ON course_enrollments (schedule_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_enrollments DROP FOREIGN KEY FK_B8B6F1E6A40BC2D5');
        $this->addSql('DROP INDEX IDX_B8B6F1E6A40BC2D5 ON course_enrollments');
        $this->addSql('ALTER TABLE course_enrollments DROP schedule_id');
    }
}
