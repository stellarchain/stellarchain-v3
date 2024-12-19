<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219194914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aggregated_metrics ALTER metric_id TYPE INT USING metric_id::integer');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER total_value TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER total_value SET NOT NULL');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER avg_value TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER avg_value SET NOT NULL');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER max_value TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER max_value SET NOT NULL');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER min_value TYPE INT');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER min_value SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER metric_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER total_value TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER total_value DROP NOT NULL');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER total_value TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER avg_value TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER avg_value DROP NOT NULL');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER avg_value TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER max_value TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER max_value DROP NOT NULL');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER max_value TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER min_value TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER min_value DROP NOT NULL');
    }
}
