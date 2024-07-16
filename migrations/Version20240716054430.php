<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240716054430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE job_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE job_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE location_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE job (id INT NOT NULL, location_id INT NOT NULL, user_id INT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, salary INT DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FBD8E0F864D218E ON job (location_id)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F89D86650F ON job (user_id)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F812469DE2 ON job (category_id)');
        $this->addSql('CREATE TABLE job_category (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE location (id INT NOT NULL, name VARCHAR(255) NOT NULL, continent VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F864D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F89D86650F FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F812469DE2 FOREIGN KEY (category_id) REFERENCES job_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE job_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE job_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE location_id_seq CASCADE');
        $this->addSql('ALTER TABLE job DROP CONSTRAINT FK_FBD8E0F864D218E');
        $this->addSql('ALTER TABLE job DROP CONSTRAINT FK_FBD8E0F89D86650F');
        $this->addSql('ALTER TABLE job DROP CONSTRAINT FK_FBD8E0F812469DE2');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE job_category');
        $this->addSql('DROP TABLE location');
    }
}
