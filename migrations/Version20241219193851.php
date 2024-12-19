<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219193851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        /* $this->addSql('DROP SEQUENCE trade_id_seq CASCADE'); */
        /* $this->addSql('DROP SEQUENCE ledger_stat_id_seq CASCADE'); */
        /* $this->addSql('CREATE SEQUENCE history_assets_id_seq INCREMENT BY 1 MINVALUE 1 START 1'); */
        /* $this->addSql('CREATE SEQUENCE history_ledgers_id_seq INCREMENT BY 1 MINVALUE 1 START 1'); */
        /* $this->addSql('CREATE SEQUENCE history_operations_id_seq INCREMENT BY 1 MINVALUE 1 START 1'); */
        /* $this->addSql('CREATE SEQUENCE history_transactions_id_seq INCREMENT BY 1 MINVALUE 1 START 1'); */
        /* $this->addSql('CREATE TABLE accounts (account_id VARCHAR(255) NOT NULL, balance INT NOT NULL, selling_liabilities INT NOT NULL, buying_liabilities INT NOT NULL, home_domain VARCHAR(255) NOT NULL, PRIMARY KEY(account_id))'); */
        /* $this->addSql('CREATE TABLE exp_asset_stats (asset_type INT NOT NULL, asset_code VARCHAR(255) NOT NULL, asset_issuer VARCHAR(255) NOT NULL, accounts JSON NOT NULL, balances JSON NOT NULL, contract_id BYTEA NOT NULL, PRIMARY KEY(asset_type, asset_code, asset_issuer))'); */
        /* $this->addSql('CREATE UNIQUE INDEX UNIQ_22DC140468BA92E19371F5504557C008 ON exp_asset_stats (asset_type, asset_code, asset_issuer)'); */
        /* $this->addSql('CREATE TABLE history_assets (id INT NOT NULL, asset_type VARCHAR(255) NOT NULL, asset_code VARCHAR(255) NOT NULL, asset_issuer VARCHAR(255) NOT NULL, PRIMARY KEY(id))'); */
        /* $this->addSql('CREATE TABLE history_ledgers (id INT NOT NULL, sequence INT NOT NULL, transaction_count INT NOT NULL, operation_count INT NOT NULL, closed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, total_coins INT NOT NULL, successful_transaction_count INT NOT NULL, failed_transaction_count INT NOT NULL, PRIMARY KEY(id))'); */
        /* $this->addSql('COMMENT ON COLUMN history_ledgers.closed_at IS \'(DC2Type:datetime_immutable)\''); */
        /* $this->addSql('CREATE TABLE history_operations (id VARCHAR(255) NOT NULL, transaction_id INT NOT NULL, type INT NOT NULL, details JSON NOT NULL, is_payment BOOLEAN NOT NULL, PRIMARY KEY(id))'); */
        /* $this->addSql('CREATE TABLE history_trades (history_operation_id INT NOT NULL, base_account_id INT NOT NULL, ledger_closed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "order" INT NOT NULL, base_asset_id INT NOT NULL, base_amount INT NOT NULL, counter_account_id INT NOT NULL, counter_asset_id INT NOT NULL, counter_amount INT NOT NULL, base_is_seller BOOLEAN NOT NULL, price_n INT NOT NULL, price_d INT NOT NULL, base_offer_id INT NOT NULL, counter_offer_id INT NOT NULL, trade_type INT NOT NULL, base_is_exact INT NOT NULL, PRIMARY KEY(history_operation_id))'); */
        /* $this->addSql('COMMENT ON COLUMN history_trades.ledger_closed_at IS \'(DC2Type:datetime_immutable)\''); */
        /* $this->addSql('CREATE TABLE history_transactions (id INT NOT NULL, transaction_hash VARCHAR(64) NOT NULL, ledger_sequence INT NOT NULL, PRIMARY KEY(id))'); */
        /* $this->addSql('CREATE TABLE offers (seller_id VARCHAR(255) NOT NULL, offer_id INT NOT NULL, selling_asset TEXT DEFAULT NULL, buying_asset TEXT DEFAULT NULL, amount INT NOT NULL, pricen INT NOT NULL, priced INT NOT NULL, price NUMERIC(10, 0) NOT NULL, flags INT NOT NULL, PRIMARY KEY(seller_id))'); */
        /* $this->addSql('ALTER TABLE trade DROP CONSTRAINT fk_7e1a43664f31bab9'); */
        /* $this->addSql('ALTER TABLE trade DROP CONSTRAINT fk_7e1a4366493d6243'); */
        /* $this->addSql('DROP TABLE ledger_stat'); */
        /* $this->addSql('DROP TABLE trade'); */
        $this->addSql('ALTER TABLE aggregated_metrics ALTER metric_id TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE aggregated_metrics ALTER metric_id TYPE INT');
    }
}
