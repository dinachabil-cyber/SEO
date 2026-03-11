<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reference_section and page_section tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable('reference_section')) {
            $this->addSql('CREATE TABLE reference_section (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
        
        if (!$schema->hasTable('page_section')) {
            $this->addSql('CREATE TABLE page_section (id INT AUTO_INCREMENT NOT NULL, page_id INT NOT NULL, reference_section_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, position INT NOT NULL, data JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_59766770C4663E4 (page_id), INDEX IDX_59766770F73A70AB (reference_section_id), INDEX IDX_59766770C4663E4B3750AF0 (page_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
        
        $pageSectionTable = $schema->getTable('page_section');
        if (!$pageSectionTable->hasForeignKey('FK_59766770C4663E4')) {
            $this->addSql('ALTER TABLE page_section ADD CONSTRAINT FK_59766770C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        }
        
        if (!$pageSectionTable->hasForeignKey('FK_59766770F73A70AB')) {
            $this->addSql('ALTER TABLE page_section ADD CONSTRAINT FK_59766770F73A70AB FOREIGN KEY (reference_section_id) REFERENCES reference_section (id) ON DELETE SET NULL');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_section DROP FOREIGN KEY FK_59766770C4663E4');
        $this->addSql('ALTER TABLE page_section DROP FOREIGN KEY FK_59766770F73A70AB');
        $this->addSql('DROP TABLE reference_section');
        $this->addSql('DROP TABLE page_section');
    }
}
