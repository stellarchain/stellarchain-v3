<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723155953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD round_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD round_phase_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD scf_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEA6005CA0 FOREIGN KEY (round_id) REFERENCES round (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEC4BEAED2 FOREIGN KEY (round_phase_id) REFERENCES round_phase (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEA6005CA0 ON project (round_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEC4BEAED2 ON project (round_phase_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EEA6005CA0');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EEC4BEAED2');
        $this->addSql('DROP INDEX IDX_2FB3D0EEA6005CA0');
        $this->addSql('DROP INDEX IDX_2FB3D0EEC4BEAED2');
        $this->addSql('ALTER TABLE project DROP round_id');
        $this->addSql('ALTER TABLE project DROP round_phase_id');
        $this->addSql('ALTER TABLE project DROP status');
        $this->addSql('ALTER TABLE project DROP scf_url');
        $this->addSql('ALTER TABLE project DROP score');
    }
}
