<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240627072912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_ac6340b39d86650f RENAME TO IDX_AC6340B3A76ED395');
        $this->addSql('ALTER TABLE post ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE post ADD rank INT DEFAULT NULL');
        $this->addSql('ALTER INDEX idx_5a8a6c8d9d86650f RENAME TO IDX_5A8A6C8DA76ED395');
        $this->addSql('ALTER INDEX idx_2fb3d0ee9d86650f RENAME TO IDX_2FB3D0EEA76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER INDEX idx_ac6340b3a76ed395 RENAME TO idx_ac6340b39d86650f');
        $this->addSql('ALTER TABLE post DROP slug');
        $this->addSql('ALTER TABLE post DROP rank');
        $this->addSql('ALTER INDEX idx_5a8a6c8da76ed395 RENAME TO idx_5a8a6c8d9d86650f');
        $this->addSql('ALTER INDEX idx_2fb3d0eea76ed395 RENAME TO idx_2fb3d0ee9d86650f');
    }
}
