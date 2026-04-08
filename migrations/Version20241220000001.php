<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241220000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table and add user and status fields to site';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(180) NOT NULL,
            roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
            password VARCHAR(255) NOT NULL,
            created_at DATETIME(6) DEFAULT NULL,
            updated_at DATETIME(6) DEFAULT NULL,
            UNIQUE INDEX UNIQ_8D93D6495E237E06 (name),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE site ADD user_id INT DEFAULT NULL, ADD status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_694309E4A76ED395 ON site (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4A76ED395');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_694309E4A76ED395 ON site');
        $this->addSql('ALTER TABLE site DROP user_id, DROP status');
    }
}
