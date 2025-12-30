<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230224924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_assessment (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, personal_trainer_id INT NOT NULL, age INT DEFAULT NULL, height NUMERIC(5, 2) DEFAULT NULL, weight NUMERIC(5, 2) DEFAULT NULL, gender VARCHAR(10) DEFAULT NULL, fitness_level VARCHAR(50) DEFAULT NULL, primary_goal LONGTEXT DEFAULT NULL, secondary_goals LONGTEXT DEFAULT NULL, training_experience INT DEFAULT NULL, weekly_availability INT DEFAULT NULL, session_duration INT DEFAULT NULL, current_injuries JSON DEFAULT NULL, past_injuries JSON DEFAULT NULL, medical_conditions JSON DEFAULT NULL, medications LONGTEXT DEFAULT NULL, allergies LONGTEXT DEFAULT NULL, activity_level VARCHAR(50) DEFAULT NULL, occupation VARCHAR(50) DEFAULT NULL, sleep_hours INT DEFAULT NULL, stress_level INT DEFAULT NULL, nutrition_habits LONGTEXT DEFAULT NULL, preferred_exercises JSON DEFAULT NULL, disliked_exercises JSON DEFAULT NULL, available_equipment JSON DEFAULT NULL, training_preferences LONGTEXT DEFAULT NULL, body_fat_percentage NUMERIC(5, 2) DEFAULT NULL, muscle_mass NUMERIC(5, 2) DEFAULT NULL, circumferences JSON DEFAULT NULL, strength_tests JSON DEFAULT NULL, flexibility_tests JSON DEFAULT NULL, pt_notes LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6706003119EB6921 (client_id), INDEX IDX_67060031BBD84A56 (personal_trainer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_assessment ADD CONSTRAINT FK_6706003119EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE client_assessment ADD CONSTRAINT FK_67060031BBD84A56 FOREIGN KEY (personal_trainer_id) REFERENCES personal_trainer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_assessment DROP FOREIGN KEY FK_6706003119EB6921');
        $this->addSql('ALTER TABLE client_assessment DROP FOREIGN KEY FK_67060031BBD84A56');
        $this->addSql('DROP TABLE client_assessment');
    }
}
