<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229221400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gym_settings (id INT AUTO_INCREMENT NOT NULL, gym_id INT NOT NULL, course_schedule_start TIME NOT NULL, course_schedule_end TIME NOT NULL, time_slot_duration INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_ABE55155BD2F03 (gym_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gym_settings ADD CONSTRAINT FK_ABE55155BD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_settings DROP FOREIGN KEY FK_ABE55155BD2F03');
        $this->addSql('DROP TABLE gym_settings');
    }
}
