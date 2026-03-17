<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add unique constraint to domain column in site table
 */
final class Version20260317000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint to domain column in site table';
    }

    public function up(Schema $schema): void
    {
        $siteTable = $schema->getTable('site');
        if (!$siteTable->hasIndex('UNIQ_694309E4115F0EE5')) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_694309E4115F0EE5 ON site (domain)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_694309E4115F0EE5 ON site');
    }
}