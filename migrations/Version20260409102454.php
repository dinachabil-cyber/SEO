<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409102454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_user (event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_92589AE271F7E88B (event_id), INDEX IDX_92589AE2A76ED395 (user_id), PRIMARY KEY (event_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY `FK_140AB620F6BD1646`');
        $this->addSql('DROP INDEX IDX_page_is_published ON page');
        $this->addSql('DROP INDEX IDX_140AB6205DA37D0D ON page');
        $this->addSql('DROP INDEX UNIQ_140AB620F6BD1646989D9B62 ON page');
        $this->addSql('ALTER TABLE page DROP google_ads, DROP google_analytics, DROP google_tag_manager, CHANGE h1 h1 VARCHAR(255) NOT NULL, CHANGE meta_description meta_description VARCHAR(255) NOT NULL, CHANGE meta_title meta_title VARCHAR(255) NOT NULL, CHANGE is_published is_published TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site CHANGE hosting hosting VARCHAR(255) DEFAULT NULL, CHANGE owner_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E47E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site RENAME INDEX uniq_694309e4115f0ee5 TO UNIQ_694309E4A7A91E0B');
        $this->addSql('ALTER TABLE site RENAME INDEX idx_694309e4a76ed395 TO IDX_694309E47E3C61F9');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d6495e237e06 TO UNIQ_IDENTIFIER_NAME');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE271F7E88B');
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE2A76ED395');
        $this->addSql('DROP TABLE event_user');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620F6BD1646');
        $this->addSql('ALTER TABLE page ADD google_ads VARCHAR(255) DEFAULT NULL, ADD google_analytics VARCHAR(255) DEFAULT NULL, ADD google_tag_manager VARCHAR(255) DEFAULT NULL, CHANGE meta_title meta_title VARCHAR(255) DEFAULT NULL, CHANGE meta_description meta_description VARCHAR(255) DEFAULT NULL, CHANGE h1 h1 VARCHAR(255) DEFAULT NULL, CHANGE is_published is_published TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT `FK_140AB620F6BD1646` FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_page_is_published ON page (is_published)');
        $this->addSql('CREATE INDEX IDX_140AB6205DA37D0D ON page (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620F6BD1646989D9B62 ON page (site_id, slug)');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E47E3C61F9');
        $this->addSql('ALTER TABLE site CHANGE hosting hosting VARCHAR(255) DEFAULT \'NULL\', CHANGE owner_id owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE site RENAME INDEX idx_694309e47e3c61f9 TO IDX_694309E4A76ED395');
        $this->addSql('ALTER TABLE site RENAME INDEX uniq_694309e4a7a91e0b TO UNIQ_694309E4115F0EE5');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE `user` RENAME INDEX uniq_identifier_name TO UNIQ_8D93D6495E237E06');
    }
}
