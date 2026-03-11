<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add name column to page_section table
 */
final class Version20260310000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add name column to page_section table';
    }

    public function up(Schema $schema): void
    {
        $pageSectionTable = $schema->getTable('page_section');
        
        if (!$pageSectionTable->hasColumn('name')) {
            $pageSectionTable->addColumn('name', 'string', ['length' => 255]);
        }
    }

    public function down(Schema $schema): void
    {
        $pageSectionTable = $schema->getTable('page_section');
        
        if ($pageSectionTable->hasColumn('name')) {
            $pageSectionTable->dropColumn('name');
        }
    }
}
