<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723090811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE round ALTER start_date DROP NOT NULL');
        $this->addSql('ALTER TABLE round ALTER end_date DROP NOT NULL');
        $this->addSql('ALTER TABLE round_phase ALTER start_date DROP NOT NULL');
        $this->addSql('ALTER TABLE round_phase ALTER end_date DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE round ALTER start_date SET NOT NULL');
        $this->addSql('ALTER TABLE round ALTER end_date SET NOT NULL');
        $this->addSql('ALTER TABLE round_phase ALTER start_date SET NOT NULL');
        $this->addSql('ALTER TABLE round_phase ALTER end_date SET NOT NULL');
    }
}
