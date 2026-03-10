<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add name column to page_sections table';
    }

    public function up(Schema $schema): void
    {
        $pageSectionsTable = $schema->getTable('page_section');
        if (!$pageSectionsTable->hasColumn('name')) {
            $pageSectionsTable->addColumn('name', 'string', [
                'length' => 255,
                'notnull' => true,
                'default' => '',
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $pageSectionsTable = $schema->getTable('page_section');
        if ($pageSectionsTable->hasColumn('name')) {
            $pageSectionsTable->dropColumn('name');
        }
    }
}
