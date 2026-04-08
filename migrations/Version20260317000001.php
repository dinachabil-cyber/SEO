<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove name column from site table
 */
final class Version20260317000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove name column from site table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site DROP COLUMN name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site ADD name VARCHAR(255) NOT NULL');
    }
}