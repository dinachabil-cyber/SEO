<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260410000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Event table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, site_id INT DEFAULT NULL, INDEX IDX_3BAE0AA7F6BD1646 (site_id), UNIQUE INDEX UNIQ_3BAE0AA7989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_user (event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3B83BFEA71E2D46C (event_id), INDEX IDX_3B83BFEAA76ED395 (user_id), PRIMARY KEY(event_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_3B83BFEA71E2D46C FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_3B83BFEAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_3B83BFEA71E2D46C');
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_3B83BFEAA76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7F6BD1646');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_user');
    }
}