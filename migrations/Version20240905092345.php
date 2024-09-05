<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240905092345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE ledger_stat_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE ledger_stat (id INT NOT NULL, ledger_id INT NOT NULL, lifetime NUMERIC(5, 2) NOT NULL, operations INT NOT NULL, successful_transactions INT NOT NULL, failed_transactions INT NOT NULL, created_contracts INT DEFAULT NULL, contract_invocations INT DEFAULT NULL, transactions_second INT NOT NULL, transactions_value INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN ledger_stat.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE ledger_stat_id_seq CASCADE');
        $this->addSql('DROP TABLE ledger_stat');
    }
}
