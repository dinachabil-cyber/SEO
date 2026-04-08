<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408090030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, site_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3BAE0AA7989D9B62 (slug), INDEX IDX_3BAE0AA7F6BD1646 (site_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY `FK_140AB620F6BD1646`');
        $this->addSql('DROP INDEX IDX_140AB6205DA37D0D ON page');
        $this->addSql('DROP INDEX UNIQ_140AB620F6BD1646989D9B62 ON page');
        $this->addSql('DROP INDEX IDX_page_is_published ON page');
        $this->addSql('ALTER TABLE page DROP google_ads, DROP google_analytics, DROP google_tag_manager, CHANGE h1 h1 VARCHAR(255) NOT NULL, CHANGE meta_description meta_description VARCHAR(255) NOT NULL, CHANGE meta_title meta_title VARCHAR(255) NOT NULL, CHANGE is_published is_published TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_section DROP FOREIGN KEY `FK_98A2C6F4C4663E4`');
        $this->addSql('ALTER TABLE page_section DROP name, CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE page_section RENAME INDEX idx_98a2c6f4c4663e4 TO IDX_D713917AC4663E4');
        $this->addSql('ALTER TABLE page_section RENAME INDEX fk_59766770f73a70ab TO IDX_D713917A8C3DD635');
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY `FK_BCC6B33DA76ED395`');
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY `FK_BCC6B33DFAE5492F`');
        $this->addSql('ALTER TABLE password_reset_request CHANGE admin_note admin_note LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT FK_C5D0A95AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT FK_C5D0A95A2FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE password_reset_request RENAME INDEX idx_bcc6b33da76ed395 TO IDX_C5D0A95AA76ED395');
        $this->addSql('ALTER TABLE password_reset_request RENAME INDEX idx_bcc6b33dfae5492f TO IDX_C5D0A95A2FFD4FD3');
        $this->addSql('DROP INDEX IDX_site_technology ON site');
        $this->addSql('DROP INDEX IDX_site_hosting ON site');
        $this->addSql('DROP INDEX IDX_site_status ON site');
        $this->addSql('DROP INDEX IDX_site_domain ON site');
        $this->addSql('DROP INDEX IDX_site_is_active ON site');
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
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7F6BD1646');
        $this->addSql('DROP TABLE event');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620F6BD1646');
        $this->addSql('ALTER TABLE page ADD google_ads VARCHAR(255) DEFAULT NULL, ADD google_analytics VARCHAR(255) DEFAULT NULL, ADD google_tag_manager VARCHAR(255) DEFAULT NULL, CHANGE meta_title meta_title VARCHAR(255) DEFAULT NULL, CHANGE meta_description meta_description VARCHAR(255) DEFAULT NULL, CHANGE h1 h1 VARCHAR(255) DEFAULT NULL, CHANGE is_published is_published TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT `FK_140AB620F6BD1646` FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_140AB6205DA37D0D ON page (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620F6BD1646989D9B62 ON page (site_id, slug)');
        $this->addSql('CREATE INDEX IDX_page_is_published ON page (is_published)');
        $this->addSql('ALTER TABLE page_section ADD name VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE type type VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE page_section ADD CONSTRAINT `FK_98A2C6F4C4663E4` FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE page_section RENAME INDEX idx_d713917ac4663e4 TO IDX_98A2C6F4C4663E4');
        $this->addSql('ALTER TABLE page_section RENAME INDEX idx_d713917a8c3dd635 TO FK_59766770F73A70AB');
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY FK_C5D0A95AA76ED395');
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY FK_C5D0A95A2FFD4FD3');
        $this->addSql('ALTER TABLE password_reset_request CHANGE admin_note admin_note TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT `FK_BCC6B33DA76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT `FK_BCC6B33DFAE5492F` FOREIGN KEY (processed_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE password_reset_request RENAME INDEX idx_c5d0a95aa76ed395 TO IDX_BCC6B33DA76ED395');
        $this->addSql('ALTER TABLE password_reset_request RENAME INDEX idx_c5d0a95a2ffd4fd3 TO IDX_BCC6B33DFAE5492F');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E47E3C61F9');
        $this->addSql('ALTER TABLE site CHANGE hosting hosting VARCHAR(255) DEFAULT \'NULL\', CHANGE owner_id owner_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_site_technology ON site (technology)');
        $this->addSql('CREATE INDEX IDX_site_hosting ON site (hosting)');
        $this->addSql('CREATE INDEX IDX_site_status ON site (status)');
        $this->addSql('CREATE INDEX IDX_site_domain ON site (domain)');
        $this->addSql('CREATE INDEX IDX_site_is_active ON site (is_active)');
        $this->addSql('ALTER TABLE site RENAME INDEX uniq_694309e4a7a91e0b TO UNIQ_694309E4115F0EE5');
        $this->addSql('ALTER TABLE site RENAME INDEX idx_694309e47e3c61f9 TO IDX_694309E4A76ED395');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE `user` RENAME INDEX uniq_identifier_name TO UNIQ_8D93D6495E237E06');
    }
}
