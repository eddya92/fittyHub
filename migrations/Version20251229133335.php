<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229133335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym ADD subscription_plan VARCHAR(50) DEFAULT NULL, ADD subscription_status VARCHAR(50) DEFAULT NULL, ADD subscription_start_date DATE DEFAULT NULL, ADD subscription_end_date DATE DEFAULT NULL, ADD max_clients INT DEFAULT NULL, ADD current_clients_count INT DEFAULT NULL, ADD billing_email VARCHAR(180) DEFAULT NULL, ADD payment_method VARCHAR(50) DEFAULT NULL, ADD last_payment_date DATE DEFAULT NULL, ADD next_payment_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym DROP subscription_plan, DROP subscription_status, DROP subscription_start_date, DROP subscription_end_date, DROP max_clients, DROP current_clients_count, DROP billing_email, DROP payment_method, DROP last_payment_date, DROP next_payment_date');
    }
}
