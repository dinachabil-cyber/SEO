<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SEO fields to page entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD meta_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD google_ads_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD google_analytics_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD google_tag_manager_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP meta_keywords');
        $this->addSql('ALTER TABLE page DROP google_ads_id');
        $this->addSql('ALTER TABLE page DROP google_analytics_id');
        $this->addSql('ALTER TABLE page DROP google_tag_manager_id');
    }
}
