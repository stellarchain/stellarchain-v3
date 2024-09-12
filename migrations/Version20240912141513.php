<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912141513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE vote_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vote (id INT NOT NULL, user_id INT DEFAULT NULL, comment_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A108564A76ED395 ON vote (user_id)');
        $this->addSql('CREATE INDEX IDX_5A108564F8697D13 ON vote (comment_id)');
        $this->addSql('CREATE UNIQUE INDEX user_comment_unique ON vote (user_id, comment_id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment RENAME COLUMN votes TO votes_count');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE vote_id_seq CASCADE');
        $this->addSql('ALTER TABLE vote DROP CONSTRAINT FK_5A108564A76ED395');
        $this->addSql('ALTER TABLE vote DROP CONSTRAINT FK_5A108564F8697D13');
        $this->addSql('DROP TABLE vote');
        $this->addSql('ALTER TABLE comment RENAME COLUMN votes_count TO votes');
    }
}
