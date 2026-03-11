<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove name column and add hebergement column to site table
 */
final class Version20260311000006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove name column and add hebergement column to site table';
    }

    public function up(Schema $schema): void
    {
        $siteTable = $schema->getTable('site');
        
        if ($siteTable->hasColumn('name')) {
            $siteTable->dropColumn('name');
        }
        
        if (!$siteTable->hasColumn('hebergement')) {
            $siteTable->addColumn('hebergement', 'string', ['length' => 255, 'notnull' => false]);
        }
    }

    public function down(Schema $schema): void
    {
        $siteTable = $schema->getTable('site');
        
        if (!$siteTable->hasColumn('name')) {
            $siteTable->addColumn('name', 'string', ['length' => 255]);
        }
        
        if ($siteTable->hasColumn('hebergement')) {
            $siteTable->dropColumn('hebergement');
        }
    }
}
