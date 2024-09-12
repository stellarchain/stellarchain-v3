<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912054010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE community_post_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE community_post (id INT NOT NULL, user_id INT NOT NULL, content TEXT DEFAULT NULL, followers INT DEFAULT NULL, posts INT DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9BDB8647A76ED395 ON community_post (user_id)');
        $this->addSql('COMMENT ON COLUMN community_post.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN community_post.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE community_post ADD CONSTRAINT FK_9BDB8647A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE community_post_id_seq CASCADE');
        $this->addSql('ALTER TABLE community_post DROP CONSTRAINT FK_9BDB8647A76ED395');
        $this->addSql('DROP TABLE community_post');
    }
}
