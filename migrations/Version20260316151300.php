<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316151300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename user column to owner in site table';
    }

    public function up(Schema $schema): void
    {
        // Rename the column
        $this->addSql('ALTER TABLE site CHANGE user_id owner_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Rename the column back
        $this->addSql('ALTER TABLE site CHANGE owner_id user_id INT DEFAULT NULL');
    }
}
