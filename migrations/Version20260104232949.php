<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104232949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_attendance ADD course_session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gym_attendance ADD CONSTRAINT FK_D6180BA1BEDDA25C FOREIGN KEY (course_session_id) REFERENCES course_sessions (id)');
        $this->addSql('CREATE INDEX IDX_D6180BA1BEDDA25C ON gym_attendance (course_session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_attendance DROP FOREIGN KEY FK_D6180BA1BEDDA25C');
        $this->addSql('DROP INDEX IDX_D6180BA1BEDDA25C ON gym_attendance');
        $this->addSql('ALTER TABLE gym_attendance DROP course_session_id');
    }
}
