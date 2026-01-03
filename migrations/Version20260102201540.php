<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102201540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course_waiting_lists (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, user_id INT NOT NULL, status VARCHAR(50) NOT NULL, position INT NOT NULL, joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', notified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_24DEF038A40BC2D5 (schedule_id), INDEX IDX_24DEF038A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course_waiting_lists ADD CONSTRAINT FK_24DEF038A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES course_schedules (id)');
        $this->addSql('ALTER TABLE course_waiting_lists ADD CONSTRAINT FK_24DEF038A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course_waiting_lists DROP FOREIGN KEY FK_24DEF038A40BC2D5');
        $this->addSql('ALTER TABLE course_waiting_lists DROP FOREIGN KEY FK_24DEF038A76ED395');
        $this->addSql('DROP TABLE course_waiting_lists');
    }
}
