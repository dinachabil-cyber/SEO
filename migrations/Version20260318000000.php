<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_enabled field to user table and create password_reset_request table';
    }

    public function up(Schema $schema): void
    {
        // Add is_enabled column to user table
        $this->addSql('ALTER TABLE `user` ADD is_enabled TINYINT(1) DEFAULT 1 NOT NULL');

        // Create password_reset_request table
        $this->addSql('CREATE TABLE password_reset_request (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            processed_by_id INT DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            requested_at DATETIME NOT NULL,
            processed_at DATETIME DEFAULT NULL,
            admin_note TEXT DEFAULT NULL,
            INDEX IDX_BCC6B33DA76ED395 (user_id),
            INDEX IDX_BCC6B33DFAE5492F (processed_by_id),
            PRIMARY KEY(id)
        ) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT FK_BCC6B33DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT FK_BCC6B33DFAE5492F FOREIGN KEY (processed_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY FK_BCC6B33DA76ED395');
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY FK_BCC6B33DFAE5492F');
        $this->addSql('DROP TABLE password_reset_request');
        $this->addSql('ALTER TABLE `user` DROP COLUMN is_enabled');
    }
}
