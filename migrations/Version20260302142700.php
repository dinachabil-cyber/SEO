<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302142700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SEO fields to Page entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD h1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD target_keywords LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD meta_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD internal_links LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP h1');
        $this->addSql('ALTER TABLE page DROP target_keywords');
        $this->addSql('ALTER TABLE page DROP meta_description');
        $this->addSql('ALTER TABLE page DROP internal_links');
    }
}
