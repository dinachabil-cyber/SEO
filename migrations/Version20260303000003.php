<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Update columns to datetime_immutable
 */
final class Version20260303000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update created_at and updated_at columns to datetime_immutable';
    }

    public function up(Schema $schema): void
    {
        // Update site table
        $siteTable = $schema->getTable('site');
        if ($siteTable->hasColumn('created_at') && $siteTable->getColumn('created_at')->getType()->getName() === 'datetime') {
            $siteTable->changeColumn('created_at', ['type' => 'datetime_immutable']);
        }
        if ($siteTable->hasColumn('updated_at') && $siteTable->getColumn('updated_at')->getType()->getName() === 'datetime') {
            $siteTable->changeColumn('updated_at', ['type' => 'datetime_immutable']);
        }
        
        // Update page table
        $pageTable = $schema->getTable('page');
        if ($pageTable->hasColumn('created_at') && $pageTable->getColumn('created_at')->getType()->getName() === 'datetime') {
            $pageTable->changeColumn('created_at', ['type' => 'datetime_immutable']);
        }
        if ($pageTable->hasColumn('updated_at') && $pageTable->getColumn('updated_at')->getType()->getName() === 'datetime') {
            $pageTable->changeColumn('updated_at', ['type' => 'datetime_immutable']);
        }
    }

    public function down(Schema $schema): void
    {
        // Update site table
        $siteTable = $schema->getTable('site');
        if ($siteTable->hasColumn('created_at') && $siteTable->getColumn('created_at')->getType()->getName() === 'datetime_immutable') {
            $siteTable->changeColumn('created_at', ['type' => 'datetime']);
        }
        if ($siteTable->hasColumn('updated_at') && $siteTable->getColumn('updated_at')->getType()->getName() === 'datetime_immutable') {
            $siteTable->changeColumn('updated_at', ['type' => 'datetime']);
        }
        
        // Update page table
        $pageTable = $schema->getTable('page');
        if ($pageTable->hasColumn('created_at') && $pageTable->getColumn('created_at')->getType()->getName() === 'datetime_immutable') {
            $pageTable->changeColumn('created_at', ['type' => 'datetime']);
        }
        if ($pageTable->hasColumn('updated_at') && $pageTable->getColumn('updated_at')->getType()->getName() === 'datetime_immutable') {
            $pageTable->changeColumn('updated_at', ['type' => 'datetime']);
        }
    }
}
