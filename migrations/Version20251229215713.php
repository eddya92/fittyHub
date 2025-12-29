<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229215713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add course_categories table and update gym_courses';
    }

    public function up(Schema $schema): void
    {
        // Change category from string to foreign key in gym_courses
        $this->addSql('ALTER TABLE gym_courses CHANGE category category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gym_courses ADD CONSTRAINT FK_DD79EAE12469DE2 FOREIGN KEY (category_id) REFERENCES course_categories (id)');
        $this->addSql('CREATE INDEX IDX_DD79EAE12469DE2 ON gym_courses (category_id)');
    }

    public function down(Schema $schema): void
    {
        // Revert changes
        $this->addSql('ALTER TABLE gym_courses DROP FOREIGN KEY FK_DD79EAE12469DE2');
        $this->addSql('DROP INDEX IDX_DD79EAE12469DE2 ON gym_courses');
        $this->addSql('ALTER TABLE gym_courses CHANGE category_id category VARCHAR(100) NOT NULL');

        $this->addSql('ALTER TABLE course_categories DROP FOREIGN KEY FK_A878ED17BD2F03');
        $this->addSql('DROP TABLE course_categories');
    }
}
