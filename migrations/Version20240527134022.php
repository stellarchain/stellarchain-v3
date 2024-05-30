<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240527134022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_like (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, user_id_id INT NOT NULL, project_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_95F288AB9D86650F ON project_like (user_id_id)');
        $this->addSql('CREATE INDEX IDX_95F288AB166D1F9C ON project_like (project_id)');
        $this->addSql('ALTER TABLE project_like ADD CONSTRAINT FK_95F288AB9D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_like ADD CONSTRAINT FK_95F288AB166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_like DROP CONSTRAINT FK_95F288AB9D86650F');
        $this->addSql('ALTER TABLE project_like DROP CONSTRAINT FK_95F288AB166D1F9C');
        $this->addSql('DROP TABLE project_like');
    }
}
