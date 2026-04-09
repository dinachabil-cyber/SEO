<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add event_assigned_users ManyToMany relationship table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_assigned_users (event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_EA9EC4E71D89352C (event_id), INDEX IDX_EA9EC4E7A76ED395 (user_id), PRIMARY KEY (event_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event_assigned_users ADD CONSTRAINT FK_EA9EC4E71D89352C FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_assigned_users ADD CONSTRAINT FK_EA9EC4E7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_assigned_users DROP FOREIGN KEY FK_EA9EC4E71D89352C');
        $this->addSql('ALTER TABLE event_assigned_users DROP FOREIGN KEY FK_EA9EC4E7A76ED395');
        $this->addSql('DROP TABLE event_assigned_users');
    }
}