<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add imprint fields to sites table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site ADD company_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD address VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD phone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD legal_representative VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE site ADD registration_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site DROP company_name');
        $this->addSql('ALTER TABLE site DROP address');
        $this->addSql('ALTER TABLE site DROP phone');
        $this->addSql('ALTER TABLE site DROP email');
        $this->addSql('ALTER TABLE site DROP legal_representative');
        $this->addSql('ALTER TABLE site DROP registration_number');
    }
}