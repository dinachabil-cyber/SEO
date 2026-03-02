<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to create page table with OneToMany relationship to site
 */
final class Version20260302135300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create page table with OneToMany relationship to site';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, url VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, INDEX IDX_140AB620F6BD1646 (site_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE page');
    }
}
