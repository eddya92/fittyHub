<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229134517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_membership DROP FOREIGN KEY FK_3D04D4B59B8CE200');
        $this->addSql('DROP INDEX IDX_3D04D4B59B8CE200 ON gym_membership');
        $this->addSql('ALTER TABLE gym_membership DROP subscription_plan_id, DROP auto_renew, DROP price, DROP payment_method, DROP last_payment_date, DROP next_payment_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_membership ADD subscription_plan_id INT NOT NULL, ADD auto_renew TINYINT(1) NOT NULL, ADD price NUMERIC(10, 2) NOT NULL, ADD payment_method VARCHAR(50) DEFAULT NULL, ADD last_payment_date DATE DEFAULT NULL, ADD next_payment_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE gym_membership ADD CONSTRAINT FK_3D04D4B59B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3D04D4B59B8CE200 ON gym_membership (subscription_plan_id)');
    }
}
