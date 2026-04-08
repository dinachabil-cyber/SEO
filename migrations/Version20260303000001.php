<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update existing tables with createdAt and updatedAt fields';
    }

    public function up(Schema $schema): void
    {
        // Check if createdAt and updatedAt columns exist before adding
        $siteTable = $schema->getTable('site');
        if (!$siteTable->hasColumn('created_at')) {
            $siteTable->addColumn('created_at', 'datetime_immutable')->setNotnull(true);
        }
        if (!$siteTable->hasColumn('updated_at')) {
            $siteTable->addColumn('updated_at', 'datetime_immutable')->setNotnull(true);
        }
        
        $pageTable = $schema->getTable('page');
        if (!$pageTable->hasColumn('created_at')) {
            $pageTable->addColumn('created_at', 'datetime_immutable')->setNotnull(true);
        }
        if (!$pageTable->hasColumn('updated_at')) {
            $pageTable->addColumn('updated_at', 'datetime_immutable')->setNotnull(true);
        }
        
        // Check if unique index on site_id and slug exists on page table
        if (!$pageTable->hasIndex('UNIQ_140AB620F6BD1646989D9B62')) {
            $pageTable->addUniqueIndex(['site_id', 'slug'], 'UNIQ_140AB620F6BD1646989D9B62');
        }
    }

    public function down(Schema $schema): void
    {
        $siteTable = $schema->getTable('site');
        if ($siteTable->hasColumn('created_at')) {
            $siteTable->dropColumn('created_at');
        }
        if ($siteTable->hasColumn('updated_at')) {
            $siteTable->dropColumn('updated_at');
        }
        
        $pageTable = $schema->getTable('page');
        if ($pageTable->hasColumn('created_at')) {
            $pageTable->dropColumn('created_at');
        }
        if ($pageTable->hasColumn('updated_at')) {
            $pageTable->dropColumn('updated_at');
        }
        
        if ($pageTable->hasIndex('UNIQ_140AB620F6BD1646989D9B62')) {
            $pageTable->dropIndex('UNIQ_140AB620F6BD1646989D9B62');
        }
    }
}
