<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103142032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, gym_id INT NOT NULL, membership_id INT DEFAULT NULL, course_enrollment_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, payment_date DATETIME NOT NULL, payment_method VARCHAR(50) NOT NULL, payment_type VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, transaction_reference VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6D28840DA76ED395 (user_id), INDEX IDX_6D28840DBD2F03 (gym_id), INDEX IDX_6D28840D1FB354CD (membership_id), INDEX IDX_6D28840D4CF0F682 (course_enrollment_id), INDEX IDX_6D28840DB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DBD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D1FB354CD FOREIGN KEY (membership_id) REFERENCES gym_membership (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D4CF0F682 FOREIGN KEY (course_enrollment_id) REFERENCES course_enrollments (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA76ED395');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DBD2F03');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D1FB354CD');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D4CF0F682');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DB03A8386');
        $this->addSql('DROP TABLE payment');
    }
}
