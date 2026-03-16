<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add name column to site table';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->getTable('site')->hasColumn('name')) {
            $this->addSql('ALTER TABLE site ADD name VARCHAR(255) NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->getTable('site')->hasColumn('name')) {
            $this->addSql('ALTER TABLE site DROP name');
        }
    }
}
