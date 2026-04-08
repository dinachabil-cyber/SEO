<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add missing database indexes for frequently searched fields
 */
final class Version20260313000011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing database indexes for frequently searched fields';
    }

    public function up(Schema $schema): void
    {
        // Site table indexes
        $siteTable = $schema->getTable('site');
        if (!$siteTable->hasIndex('IDX_site_domain')) {
            $siteTable->addIndex(['domain'], 'IDX_site_domain');
        }
        if (!$siteTable->hasIndex('IDX_site_technology')) {
            $siteTable->addIndex(['technology'], 'IDX_site_technology');
        }
        if (!$siteTable->hasIndex('IDX_site_hosting')) {
            $siteTable->addIndex(['hosting'], 'IDX_site_hosting');
        }
        if (!$siteTable->hasIndex('IDX_site_status')) {
            $siteTable->addIndex(['status'], 'IDX_site_status');
        }
        if (!$siteTable->hasIndex('IDX_site_is_active')) {
            $siteTable->addIndex(['is_active'], 'IDX_site_is_active');
        }
        
        // Page table indexes
        $pageTable = $schema->getTable('page');
        if (!$pageTable->hasIndex('IDX_page_is_published')) {
            $pageTable->addIndex(['is_published'], 'IDX_page_is_published');
        }
        
        // PageSection table index (already has page_id and page_id+position indexes)
    }

    public function down(Schema $schema): void
    {
        $siteTable = $schema->getTable('site');
        if ($siteTable->hasIndex('IDX_site_domain')) {
            $siteTable->dropIndex('IDX_site_domain');
        }
        if ($siteTable->hasIndex('IDX_site_technology')) {
            $siteTable->dropIndex('IDX_site_technology');
        }
        if ($siteTable->hasIndex('IDX_site_hosting')) {
            $siteTable->dropIndex('IDX_site_hosting');
        }
        if ($siteTable->hasIndex('IDX_site_status')) {
            $siteTable->dropIndex('IDX_site_status');
        }
        if ($siteTable->hasIndex('IDX_site_is_active')) {
            $siteTable->dropIndex('IDX_site_is_active');
        }
        
        $pageTable = $schema->getTable('page');
        if ($pageTable->hasIndex('IDX_page_is_published')) {
            $pageTable->dropIndex('IDX_page_is_published');
        }
    }
}
