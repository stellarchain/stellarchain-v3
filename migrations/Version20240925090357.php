<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925090357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE community_user (community_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(community_id, user_id))');
        $this->addSql('CREATE INDEX IDX_4CC23C83FDA7B0BF ON community_user (community_id)');
        $this->addSql('CREATE INDEX IDX_4CC23C83A76ED395 ON community_user (user_id)');
        $this->addSql('CREATE TABLE project_user (project_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(project_id, user_id))');
        $this->addSql('CREATE INDEX IDX_B4021E51166D1F9C ON project_user (project_id)');
        $this->addSql('CREATE INDEX IDX_B4021E51A76ED395 ON project_user (user_id)');
        $this->addSql('CREATE TABLE user_follows_user (follower_id INT NOT NULL, followed_id INT NOT NULL, PRIMARY KEY(follower_id, followed_id))');
        $this->addSql('CREATE INDEX IDX_35F0F5A7AC24F853 ON user_follows_user (follower_id)');
        $this->addSql('CREATE INDEX IDX_35F0F5A7D956F010 ON user_follows_user (followed_id)');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_follows_user ADD CONSTRAINT FK_35F0F5A7AC24F853 FOREIGN KEY (follower_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_follows_user ADD CONSTRAINT FK_35F0F5A7D956F010 FOREIGN KEY (followed_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE community_user DROP CONSTRAINT FK_4CC23C83FDA7B0BF');
        $this->addSql('ALTER TABLE community_user DROP CONSTRAINT FK_4CC23C83A76ED395');
        $this->addSql('ALTER TABLE project_user DROP CONSTRAINT FK_B4021E51166D1F9C');
        $this->addSql('ALTER TABLE project_user DROP CONSTRAINT FK_B4021E51A76ED395');
        $this->addSql('ALTER TABLE user_follows_user DROP CONSTRAINT FK_35F0F5A7AC24F853');
        $this->addSql('ALTER TABLE user_follows_user DROP CONSTRAINT FK_35F0F5A7D956F010');
        $this->addSql('DROP TABLE community_user');
        $this->addSql('DROP TABLE project_user');
        $this->addSql('DROP TABLE user_follows_user');
    }
}
