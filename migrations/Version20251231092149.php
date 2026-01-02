<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231092149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_membership ADD subscription_plan_id INT DEFAULT NULL, ADD original_price NUMERIC(10, 2) DEFAULT NULL, ADD actual_price NUMERIC(10, 2) DEFAULT NULL, ADD bonus_months INT DEFAULT NULL, ADD discount_reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE gym_membership ADD CONSTRAINT FK_3D04D4B59B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('CREATE INDEX IDX_3D04D4B59B8CE200 ON gym_membership (subscription_plan_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_membership DROP FOREIGN KEY FK_3D04D4B59B8CE200');
        $this->addSql('DROP INDEX IDX_3D04D4B59B8CE200 ON gym_membership');
        $this->addSql('ALTER TABLE gym_membership DROP subscription_plan_id, DROP original_price, DROP actual_price, DROP bonus_months, DROP discount_reason');
    }
}
