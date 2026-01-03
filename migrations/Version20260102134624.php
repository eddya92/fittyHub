<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102134624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE membership_requests (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, gym_id INT NOT NULL, responded_by_id INT DEFAULT NULL, status VARCHAR(20) NOT NULL, message LONGTEXT DEFAULT NULL, admin_notes LONGTEXT DEFAULT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', responded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_67601AA5A76ED395 (user_id), INDEX IDX_67601AA5BD2F03 (gym_id), INDEX IDX_67601AA5296135A7 (responded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE membership_requests ADD CONSTRAINT FK_67601AA5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE membership_requests ADD CONSTRAINT FK_67601AA5BD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE membership_requests ADD CONSTRAINT FK_67601AA5296135A7 FOREIGN KEY (responded_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE gym ADD slug VARCHAR(100) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F27DBED989D9B62 ON gym (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membership_requests DROP FOREIGN KEY FK_67601AA5A76ED395');
        $this->addSql('ALTER TABLE membership_requests DROP FOREIGN KEY FK_67601AA5BD2F03');
        $this->addSql('ALTER TABLE membership_requests DROP FOREIGN KEY FK_67601AA5296135A7');
        $this->addSql('DROP TABLE membership_requests');
        $this->addSql('DROP INDEX UNIQ_7F27DBED989D9B62 ON gym');
        $this->addSql('ALTER TABLE gym DROP slug');
    }
}
