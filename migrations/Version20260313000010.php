<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313000010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add page_count column to site table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site ADD page_count INT DEFAULT 0 NOT NULL');
        
        // Initialize page count for existing sites
        $this->addSql('UPDATE site s SET page_count = (SELECT COUNT(*) FROM page p WHERE p.site_id = s.id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site DROP page_count');
    }
}
