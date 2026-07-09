<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создаёт таблицы users и auth_logs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE users (
                id INT AUTO_INCREMENT NOT NULL,
                email VARCHAR(180) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE INDEX uniq_users_email (email),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE auth_logs (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                action VARCHAR(16) NOT NULL,
                status VARCHAR(16) NOT NULL,
                ip VARCHAR(45) NOT NULL,
                user_agent VARCHAR(512) DEFAULT NULL,
                error_message LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_auth_logs_action (action),
                INDEX idx_auth_logs_status (status),
                INDEX idx_auth_logs_ip (ip),
                INDEX idx_auth_logs_created_at (created_at),
                INDEX IDX_AUTH_LOGS_USER (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE auth_logs
                ADD CONSTRAINT FK_AUTH_LOGS_USER
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_logs DROP FOREIGN KEY FK_AUTH_LOGS_USER');
        $this->addSql('DROP TABLE auth_logs');
        $this->addSql('DROP TABLE users');
    }
}
