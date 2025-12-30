<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230232901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exercise (id INT AUTO_INCREMENT NOT NULL, gym_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, category VARCHAR(100) NOT NULL, muscle_groups JSON NOT NULL, difficulty VARCHAR(50) NOT NULL, equipment VARCHAR(100) DEFAULT NULL, video_url VARCHAR(255) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, instructions LONGTEXT DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AEDAD51CBD2F03 (gym_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51CBD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_exercise ADD exercise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workout_exercise ADD CONSTRAINT FK_76AB38AAE934951A FOREIGN KEY (exercise_id) REFERENCES exercise (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_76AB38AAE934951A ON workout_exercise (exercise_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workout_exercise DROP FOREIGN KEY FK_76AB38AAE934951A');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51CBD2F03');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP INDEX IDX_76AB38AAE934951A ON workout_exercise');
        $this->addSql('ALTER TABLE workout_exercise DROP exercise_id');
    }
}
