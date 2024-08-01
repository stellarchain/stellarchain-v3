<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240801080740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE project_member_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE project_member (id INT NOT NULL, name VARCHAR(255) NOT NULL, nickname VARCHAR(255) NOT NULL, original_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE project_member_project (project_member_id INT NOT NULL, project_id INT NOT NULL, PRIMARY KEY(project_member_id, project_id))');
        $this->addSql('CREATE INDEX IDX_79F053D864AB9629 ON project_member_project (project_member_id)');
        $this->addSql('CREATE INDEX IDX_79F053D8166D1F9C ON project_member_project (project_id)');
        $this->addSql('ALTER TABLE project_member_project ADD CONSTRAINT FK_79F053D864AB9629 FOREIGN KEY (project_member_id) REFERENCES project_member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_member_project ADD CONSTRAINT FK_79F053D8166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE project_member_id_seq CASCADE');
        $this->addSql('ALTER TABLE project_member_project DROP CONSTRAINT FK_79F053D864AB9629');
        $this->addSql('ALTER TABLE project_member_project DROP CONSTRAINT FK_79F053D8166D1F9C');
        $this->addSql('DROP TABLE project_member');
        $this->addSql('DROP TABLE project_member_project');
    }
}
