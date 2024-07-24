<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723094258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE round_phase ADD round_id INT NOT NULL');
        $this->addSql('ALTER TABLE round_phase ADD CONSTRAINT FK_67CEC232A6005CA0 FOREIGN KEY (round_id) REFERENCES round (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_67CEC232A6005CA0 ON round_phase (round_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE round_phase DROP CONSTRAINT FK_67CEC232A6005CA0');
        $this->addSql('DROP INDEX IDX_67CEC232A6005CA0');
        $this->addSql('ALTER TABLE round_phase DROP round_id');
    }
}
