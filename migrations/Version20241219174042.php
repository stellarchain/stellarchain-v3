<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219174042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE aggregated_metrics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE aggregated_metrics (id INT NOT NULL, metric_id INT NOT NULL, total_entries INT DEFAULT NULL, total_value NUMERIC(10, 2) DEFAULT NULL, avg_value NUMERIC(10, 2) DEFAULT NULL, max_value NUMERIC(10, 2) DEFAULT NULL, min_value NUMERIC(10, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, timeframe INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN aggregated_metrics.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE aggregated_metrics_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_profile_id_seq CASCADE');
        $this->addSql('DROP TABLE aggregated_metrics');
    }
}
