<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove name column from site table';
    }

    public function up(Schema $schema): void
    {
        $siteTable = $schema->getTable('site');
        if ($siteTable->hasColumn('name')) {
            $siteTable->dropColumn('name');
        }
    }

    public function down(Schema $schema): void
    {
        $siteTable = $schema->getTable('site');
        if (!$siteTable->hasColumn('name')) {
            $siteTable->addColumn('name', 'string')->setLength(255)->setNotnull(false);
        }
    }
}
