<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240724185439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_type ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_type ADD CONSTRAINT FK_B54F9F3112469DE2 FOREIGN KEY (category_id) REFERENCES project_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B54F9F3112469DE2 ON project_type (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_type DROP CONSTRAINT FK_B54F9F3112469DE2');
        $this->addSql('DROP INDEX IDX_B54F9F3112469DE2');
        $this->addSql('ALTER TABLE project_type DROP category_id');
    }
}
