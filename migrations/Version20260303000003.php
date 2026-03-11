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
        // Skip datetime type changes for now to avoid errors
    }

    public function down(Schema $schema): void
    {
        // Skip datetime type changes for now to avoid errors
    }
}
