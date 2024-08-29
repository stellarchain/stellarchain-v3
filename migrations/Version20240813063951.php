<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240813063951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE asset_metric_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE asset_metric (id INT NOT NULL, asset_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, price NUMERIC(65, 18) NOT NULL, volume_24h NUMERIC(65, 18) NOT NULL, circulating_supply NUMERIC(65, 18) NOT NULL, price_change_1h NUMERIC(5, 2) NOT NULL, price_change_24h NUMERIC(5, 2) NOT NULL, price_change_7d NUMERIC(5, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6FFBA1FE5DA1941 ON asset_metric (asset_id)');
        $this->addSql('COMMENT ON COLUMN asset_metric.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE asset_metric ADD CONSTRAINT FK_6FFBA1FE5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE asset_metric_id_seq CASCADE');
        $this->addSql('ALTER TABLE asset_metric DROP CONSTRAINT FK_6FFBA1FE5DA1941');
        $this->addSql('DROP TABLE asset_metric');
    }
}
