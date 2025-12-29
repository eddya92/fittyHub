<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229131842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gym (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(100) NOT NULL, postal_code VARCHAR(10) NOT NULL, province VARCHAR(50) DEFAULT NULL, phone_number VARCHAR(20) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, vat_number VARCHAR(50) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, opening_hours JSON DEFAULT NULL, amenities JSON DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gym_admins (gym_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1E09A69BD2F03 (gym_id), INDEX IDX_1E09A69A76ED395 (user_id), PRIMARY KEY(gym_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gym_attendance (id INT AUTO_INCREMENT NOT NULL, gym_id INT NOT NULL, user_id INT NOT NULL, gym_membership_id INT NOT NULL, check_in_time DATETIME NOT NULL, check_out_time DATETIME DEFAULT NULL, duration INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D6180BA1BD2F03 (gym_id), INDEX IDX_D6180BA1A76ED395 (user_id), INDEX IDX_D6180BA147ED3DF4 (gym_membership_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gym_membership (id INT AUTO_INCREMENT NOT NULL, gym_id INT NOT NULL, user_id INT NOT NULL, subscription_plan_id INT NOT NULL, assigned_pt_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, auto_renew TINYINT(1) NOT NULL, price NUMERIC(10, 2) NOT NULL, payment_method VARCHAR(50) DEFAULT NULL, last_payment_date DATE DEFAULT NULL, next_payment_date DATE DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3D04D4B5BD2F03 (gym_id), INDEX IDX_3D04D4B5A76ED395 (user_id), INDEX IDX_3D04D4B59B8CE200 (subscription_plan_id), INDEX IDX_3D04D4B586250E79 (assigned_pt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gym_ptinvitation (id INT AUTO_INCREMENT NOT NULL, gym_id INT NOT NULL, invited_user_id INT DEFAULT NULL, invited_by_id INT NOT NULL, invited_email VARCHAR(180) NOT NULL, token VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, message LONGTEXT DEFAULT NULL, invited_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', responded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_35D647035F37A13B (token), INDEX IDX_35D64703BD2F03 (gym_id), INDEX IDX_35D64703C58DAD6E (invited_user_id), INDEX IDX_35D64703A7B4A7E3 (invited_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medical_certificate (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, gym_membership_id INT DEFAULT NULL, reviewed_by_id INT DEFAULT NULL, certificate_type VARCHAR(50) NOT NULL, issue_date DATE NOT NULL, expiry_date DATE NOT NULL, doctor_name VARCHAR(255) NOT NULL, doctor_number VARCHAR(100) DEFAULT NULL, file_path VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B36515F8A76ED395 (user_id), UNIQUE INDEX UNIQ_B36515F847ED3DF4 (gym_membership_id), INDEX IDX_B36515F8FC6B21F1 (reviewed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personal_trainer (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, gym_id INT DEFAULT NULL, is_internal TINYINT(1) NOT NULL, specialization VARCHAR(255) DEFAULT NULL, certifications JSON DEFAULT NULL, biography LONGTEXT DEFAULT NULL, hourly_rate NUMERIC(10, 2) DEFAULT NULL, experience INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, is_available_for_new_clients TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4632010BA76ED395 (user_id), INDEX IDX_4632010BBD2F03 (gym_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptclient_invitation (id INT AUTO_INCREMENT NOT NULL, personal_trainer_id INT NOT NULL, client_user_id INT DEFAULT NULL, client_email VARCHAR(180) NOT NULL, token VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, message LONGTEXT DEFAULT NULL, invited_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', responded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_C8A086495F37A13B (token), INDEX IDX_C8A08649BBD84A56 (personal_trainer_id), INDEX IDX_C8A08649F55397E8 (client_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptclient_relation (id INT AUTO_INCREMENT NOT NULL, personal_trainer_id INT NOT NULL, client_id INT NOT NULL, status VARCHAR(50) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, notes LONGTEXT DEFAULT NULL, monthly_fee NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3DB0B43BBBD84A56 (personal_trainer_id), INDEX IDX_3DB0B43B19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription_plan (id INT AUTO_INCREMENT NOT NULL, gym_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, duration INT NOT NULL, price NUMERIC(10, 2) NOT NULL, include_pt TINYINT(1) NOT NULL, pt_sessions_included INT DEFAULT NULL, max_access_per_week INT DEFAULT NULL, features JSON DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EA664B63BD2F03 (gym_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, date_of_birth DATE DEFAULT NULL, gender VARCHAR(10) DEFAULT NULL, phone_number VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, profile_image VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workout_exercise (id INT AUTO_INCREMENT NOT NULL, workout_plan_id INT NOT NULL, day_number INT NOT NULL, day_label VARCHAR(255) NOT NULL, order_position INT NOT NULL, exercise_name VARCHAR(255) NOT NULL, exercise_category VARCHAR(50) NOT NULL, muscle_group VARCHAR(50) NOT NULL, sets INT NOT NULL, reps VARCHAR(50) NOT NULL, weight NUMERIC(10, 2) DEFAULT NULL, rest_time INT DEFAULT NULL, tempo VARCHAR(50) DEFAULT NULL, rpe INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, video_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_76AB38AA945F6E33 (workout_plan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workout_plan (id INT AUTO_INCREMENT NOT NULL, personal_trainer_id INT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, plan_type VARCHAR(50) NOT NULL, goal LONGTEXT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, weeks_count INT NOT NULL, is_active TINYINT(1) NOT NULL, is_template TINYINT(1) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A5D45801BBD84A56 (personal_trainer_id), INDEX IDX_A5D4580119EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workout_session (id INT AUTO_INCREMENT NOT NULL, workout_plan_id INT NOT NULL, client_id INT NOT NULL, personal_trainer_id INT DEFAULT NULL, session_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME DEFAULT NULL, duration INT DEFAULT NULL, completed_exercises JSON DEFAULT NULL, body_weight NUMERIC(5, 2) DEFAULT NULL, mood VARCHAR(50) DEFAULT NULL, energy_level INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, rating INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AC82B97C945F6E33 (workout_plan_id), INDEX IDX_AC82B97C19EB6921 (client_id), INDEX IDX_AC82B97CBBD84A56 (personal_trainer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gym_admins ADD CONSTRAINT FK_1E09A69BD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gym_admins ADD CONSTRAINT FK_1E09A69A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gym_attendance ADD CONSTRAINT FK_D6180BA1BD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE gym_attendance ADD CONSTRAINT FK_D6180BA1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE gym_attendance ADD CONSTRAINT FK_D6180BA147ED3DF4 FOREIGN KEY (gym_membership_id) REFERENCES gym_membership (id)');
        $this->addSql('ALTER TABLE gym_membership ADD CONSTRAINT FK_3D04D4B5BD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE gym_membership ADD CONSTRAINT FK_3D04D4B5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE gym_membership ADD CONSTRAINT FK_3D04D4B59B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('ALTER TABLE gym_membership ADD CONSTRAINT FK_3D04D4B586250E79 FOREIGN KEY (assigned_pt_id) REFERENCES personal_trainer (id)');
        $this->addSql('ALTER TABLE gym_ptinvitation ADD CONSTRAINT FK_35D64703BD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE gym_ptinvitation ADD CONSTRAINT FK_35D64703C58DAD6E FOREIGN KEY (invited_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE gym_ptinvitation ADD CONSTRAINT FK_35D64703A7B4A7E3 FOREIGN KEY (invited_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE medical_certificate ADD CONSTRAINT FK_B36515F8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE medical_certificate ADD CONSTRAINT FK_B36515F847ED3DF4 FOREIGN KEY (gym_membership_id) REFERENCES gym_membership (id)');
        $this->addSql('ALTER TABLE medical_certificate ADD CONSTRAINT FK_B36515F8FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE personal_trainer ADD CONSTRAINT FK_4632010BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE personal_trainer ADD CONSTRAINT FK_4632010BBD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE ptclient_invitation ADD CONSTRAINT FK_C8A08649BBD84A56 FOREIGN KEY (personal_trainer_id) REFERENCES personal_trainer (id)');
        $this->addSql('ALTER TABLE ptclient_invitation ADD CONSTRAINT FK_C8A08649F55397E8 FOREIGN KEY (client_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ptclient_relation ADD CONSTRAINT FK_3DB0B43BBBD84A56 FOREIGN KEY (personal_trainer_id) REFERENCES personal_trainer (id)');
        $this->addSql('ALTER TABLE ptclient_relation ADD CONSTRAINT FK_3DB0B43B19EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE subscription_plan ADD CONSTRAINT FK_EA664B63BD2F03 FOREIGN KEY (gym_id) REFERENCES gym (id)');
        $this->addSql('ALTER TABLE workout_exercise ADD CONSTRAINT FK_76AB38AA945F6E33 FOREIGN KEY (workout_plan_id) REFERENCES workout_plan (id)');
        $this->addSql('ALTER TABLE workout_plan ADD CONSTRAINT FK_A5D45801BBD84A56 FOREIGN KEY (personal_trainer_id) REFERENCES personal_trainer (id)');
        $this->addSql('ALTER TABLE workout_plan ADD CONSTRAINT FK_A5D4580119EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE workout_session ADD CONSTRAINT FK_AC82B97C945F6E33 FOREIGN KEY (workout_plan_id) REFERENCES workout_plan (id)');
        $this->addSql('ALTER TABLE workout_session ADD CONSTRAINT FK_AC82B97C19EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE workout_session ADD CONSTRAINT FK_AC82B97CBBD84A56 FOREIGN KEY (personal_trainer_id) REFERENCES personal_trainer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gym_admins DROP FOREIGN KEY FK_1E09A69BD2F03');
        $this->addSql('ALTER TABLE gym_admins DROP FOREIGN KEY FK_1E09A69A76ED395');
        $this->addSql('ALTER TABLE gym_attendance DROP FOREIGN KEY FK_D6180BA1BD2F03');
        $this->addSql('ALTER TABLE gym_attendance DROP FOREIGN KEY FK_D6180BA1A76ED395');
        $this->addSql('ALTER TABLE gym_attendance DROP FOREIGN KEY FK_D6180BA147ED3DF4');
        $this->addSql('ALTER TABLE gym_membership DROP FOREIGN KEY FK_3D04D4B5BD2F03');
        $this->addSql('ALTER TABLE gym_membership DROP FOREIGN KEY FK_3D04D4B5A76ED395');
        $this->addSql('ALTER TABLE gym_membership DROP FOREIGN KEY FK_3D04D4B59B8CE200');
        $this->addSql('ALTER TABLE gym_membership DROP FOREIGN KEY FK_3D04D4B586250E79');
        $this->addSql('ALTER TABLE gym_ptinvitation DROP FOREIGN KEY FK_35D64703BD2F03');
        $this->addSql('ALTER TABLE gym_ptinvitation DROP FOREIGN KEY FK_35D64703C58DAD6E');
        $this->addSql('ALTER TABLE gym_ptinvitation DROP FOREIGN KEY FK_35D64703A7B4A7E3');
        $this->addSql('ALTER TABLE medical_certificate DROP FOREIGN KEY FK_B36515F8A76ED395');
        $this->addSql('ALTER TABLE medical_certificate DROP FOREIGN KEY FK_B36515F847ED3DF4');
        $this->addSql('ALTER TABLE medical_certificate DROP FOREIGN KEY FK_B36515F8FC6B21F1');
        $this->addSql('ALTER TABLE personal_trainer DROP FOREIGN KEY FK_4632010BA76ED395');
        $this->addSql('ALTER TABLE personal_trainer DROP FOREIGN KEY FK_4632010BBD2F03');
        $this->addSql('ALTER TABLE ptclient_invitation DROP FOREIGN KEY FK_C8A08649BBD84A56');
        $this->addSql('ALTER TABLE ptclient_invitation DROP FOREIGN KEY FK_C8A08649F55397E8');
        $this->addSql('ALTER TABLE ptclient_relation DROP FOREIGN KEY FK_3DB0B43BBBD84A56');
        $this->addSql('ALTER TABLE ptclient_relation DROP FOREIGN KEY FK_3DB0B43B19EB6921');
        $this->addSql('ALTER TABLE subscription_plan DROP FOREIGN KEY FK_EA664B63BD2F03');
        $this->addSql('ALTER TABLE workout_exercise DROP FOREIGN KEY FK_76AB38AA945F6E33');
        $this->addSql('ALTER TABLE workout_plan DROP FOREIGN KEY FK_A5D45801BBD84A56');
        $this->addSql('ALTER TABLE workout_plan DROP FOREIGN KEY FK_A5D4580119EB6921');
        $this->addSql('ALTER TABLE workout_session DROP FOREIGN KEY FK_AC82B97C945F6E33');
        $this->addSql('ALTER TABLE workout_session DROP FOREIGN KEY FK_AC82B97C19EB6921');
        $this->addSql('ALTER TABLE workout_session DROP FOREIGN KEY FK_AC82B97CBBD84A56');
        $this->addSql('DROP TABLE gym');
        $this->addSql('DROP TABLE gym_admins');
        $this->addSql('DROP TABLE gym_attendance');
        $this->addSql('DROP TABLE gym_membership');
        $this->addSql('DROP TABLE gym_ptinvitation');
        $this->addSql('DROP TABLE medical_certificate');
        $this->addSql('DROP TABLE personal_trainer');
        $this->addSql('DROP TABLE ptclient_invitation');
        $this->addSql('DROP TABLE ptclient_relation');
        $this->addSql('DROP TABLE subscription_plan');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE workout_exercise');
        $this->addSql('DROP TABLE workout_plan');
        $this->addSql('DROP TABLE workout_session');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
