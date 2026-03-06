<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add SEO and tracking fields to page table
 */
final class Version20260306000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SEO and tracking fields to page table';
    }

    public function up(Schema $schema): void
    {
        $pageTable = $schema->getTable('page');
        
        if (!$pageTable->hasColumn('meta_keywords')) {
            $pageTable->addColumn('meta_keywords', 'string', ['length' => 255, 'notnull' => false]);
        }
        
        if (!$pageTable->hasColumn('google_ads')) {
            $pageTable->addColumn('google_ads', 'string', ['length' => 255, 'notnull' => false]);
        }
        
        if (!$pageTable->hasColumn('google_analytics')) {
            $pageTable->addColumn('google_analytics', 'string', ['length' => 255, 'notnull' => false]);
        }
        
        if (!$pageTable->hasColumn('google_tag_manager')) {
            $pageTable->addColumn('google_tag_manager', 'string', ['length' => 255, 'notnull' => false]);
        }
    }

    public function down(Schema $schema): void
    {
        $pageTable = $schema->getTable('page');
        
        if ($pageTable->hasColumn('meta_keywords')) {
            $pageTable->dropColumn('meta_keywords');
        }
        
        if ($pageTable->hasColumn('google_ads')) {
            $pageTable->dropColumn('google_ads');
        }
        
        if ($pageTable->hasColumn('google_analytics')) {
            $pageTable->dropColumn('google_analytics');
        }
        
        if ($pageTable->hasColumn('google_tag_manager')) {
            $pageTable->dropColumn('google_tag_manager');
        }
    }
}
