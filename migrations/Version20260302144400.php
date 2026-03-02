<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302144400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update entities to match SEO project requirements';
    }

    public function up(Schema $schema): void
    {
        // Update Site entity
        $this->addSql('ALTER TABLE site DROP page');
        $this->addSql('ALTER TABLE site ADD domain VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE site ADD default_locale VARCHAR(5) DEFAULT \'fr\' NOT NULL');
        $this->addSql('ALTER TABLE site ADD is_active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_694309E4115F0EE5 ON site (domain)');

        // Update Page entity
        $this->addSql('ALTER TABLE page DROP url, DROP title, DROP target_keywords, DROP internal_links');
        $this->addSql('ALTER TABLE page ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE page ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD meta_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD h1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD is_published TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('CREATE INDEX IDX_140AB6205DA37D0D ON page (slug)');

        // Create PageSection entity
        $this->addSql('CREATE TABLE page_section (
            id INT AUTO_INCREMENT NOT NULL, 
            page_id INT NOT NULL, 
            type VARCHAR(20) NOT NULL, 
            position INT NOT NULL, 
            data JSON NOT NULL, 
            INDEX IDX_98A2C6F4C4663E4 (page_id), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page_section ADD CONSTRAINT FK_98A2C6F4C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
    }

    public function down(Schema $schema): void
    {
        // Reverse PageSection entity
        $this->addSql('DROP TABLE page_section');

        // Reverse Page entity
        $this->addSql('DROP INDEX IDX_140AB6205DA37D0D ON page');
        $this->addSql('ALTER TABLE page ADD url VARCHAR(255) NOT NULL, ADD title VARCHAR(255) DEFAULT NULL, ADD target_keywords LONGTEXT DEFAULT NULL, ADD internal_links LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE page DROP slug, DROP meta_title, DROP meta_description, DROP h1, DROP is_published');

        // Reverse Site entity
        $this->addSql('DROP INDEX UNIQ_694309E4115F0EE5 ON site');
        $this->addSql('ALTER TABLE site ADD page INT NOT NULL');
        $this->addSql('ALTER TABLE site DROP domain, DROP default_locale, DROP is_active');
    }
}
