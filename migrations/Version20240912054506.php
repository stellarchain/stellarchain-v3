<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912054506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE community_post ADD community_id INT NOT NULL');
        $this->addSql('ALTER TABLE community_post ADD CONSTRAINT FK_9BDB8647FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9BDB8647FDA7B0BF ON community_post (community_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE community_post DROP CONSTRAINT FK_9BDB8647FDA7B0BF');
        $this->addSql('DROP INDEX IDX_9BDB8647FDA7B0BF');
        $this->addSql('ALTER TABLE community_post DROP community_id');
    }
}
