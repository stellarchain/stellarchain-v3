<?php

namespace App\Service;

use Google\Cloud\BigQuery\BigQueryClient;

class StellarBigQuery
{

    protected BigQueryClient $bigQuery;


    public function __construct(string $googleCloudKeyFile)
    {
        $keyFileContent = '';
        $this->bigQuery = new BigQueryClient(['keyFile' => $keyFileContent]);
    }

    /**
     * @param int $limit
     * @param int $offset
     */
    public function getAccounts($limit = 1000, $offset = 0): array
    {
        $query = "SELECT account_id, balance FROM `crypto-stellar.crypto_stellar.accounts` LIMIT $limit OFFSET $offset";
        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);
        $accounts = [];
        foreach ($queryResults as $row) {
            $accounts[] = $row;
        }
        return $accounts;
    }

    public function totalAccounts(): int
    {
        $query = "SELECT COUNT(*) as total FROM `crypto-stellar.crypto_stellar.accounts`";
        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);
        foreach ($queryResults as $row) {
            return $row['total'];
        }
    }

    public function totalAssets(): int
    {
        $query = "SELECT COUNT(*) as total FROM `crypto-stellar.crypto_stellar.history_assets`";
        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);
        foreach ($queryResults as $row) {
            return $row['total'];
        }
    }


    public function dexTrades(): int
    {
        $query = "SELECT COUNT(*) as total
                FROM `crypto-stellar.crypto_stellar.history_trades`
                WHERE ledger_closed_at > TIMESTAMP_SUB(CURRENT_TIMESTAMP(), INTERVAL 10 MINUTE)";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);
        foreach ($queryResults as $row) {
            return $row['total'];
        }
    }


    public function getBlockchainSize(): float
    {
        $query = "SELECT SUM(size_bytes)/POW(10,12) as size
            FROM `crypto-stellar.crypto_stellar.__TABLES__`
            WHERE table_id IN (
              'accounts_current',
              'accounts_signers_current',
              'offers_current',
              'trust_lines_current',
              'liquidity_pools_current',
              'history_transactions',
              'history_ledgers',
              'history_operations',
              'history_assets',
              'history_effects',
              'history_trades',
              'offers_current'
        )";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);
        foreach ($queryResults as $row) {
            return $row['size'];
        }
    }

    public function top100ActiveAddressesAvgBalance(): float
    {
        $query = "
            SELECT AVG(balance) AS avg_balance
            FROM (
                SELECT balance
                FROM `crypto-stellar.crypto_stellar.accounts`
                WHERE balance != 0
                ORDER BY balance DESC
                LIMIT 100
            ) AS subquery
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        foreach ($queryResults as $row) {
            return $row['avg_balance'];
        }
    }

    public function activeAddressesCount(): int
    {
        $query = "
            SELECT COUNT(*) AS active_count
            FROM `crypto-stellar.crypto_stellar.accounts`
            WHERE balance != 0
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        foreach ($queryResults as $row) {
            return $row['active_count'];
        }
    }


    public function inactiveAddressesCount(): int
    {
        $query = "
        SELECT COUNT(*) AS inactive_count
        FROM `crypto-stellar.crypto_stellar.accounts`
        WHERE balance = 0
    ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        foreach ($queryResults as $row) {
            return $row['inactive_count'];
        }
    }

    public function averageLedgerSizeLast5Minutes(): float
    {
        $query = "
            SELECT AVG(ledger_size) AS avg_ledger_size
            FROM (
                SELECT SUM(LENGTH(TO_JSON_STRING(history_ledgers))) +
                       SUM(LENGTH(TO_JSON_STRING(history_transactions))) +
                       SUM(LENGTH(TO_JSON_STRING(history_operations))) AS ledger_size
                FROM `crypto-stellar.crypto_stellar.history_ledgers` AS history_ledgers
                JOIN `crypto-stellar.crypto_stellar.history_transactions` AS history_transactions
                  ON history_ledgers.sequence = history_transactions.ledger_sequence
                JOIN `crypto-stellar.crypto_stellar.history_operations` AS history_operations
                  ON history_operations.transaction_id = history_transactions.id
                WHERE TIMESTAMP(history_ledgers.closed_at) > TIMESTAMP_SUB(CURRENT_TIMESTAMP(), INTERVAL 5 MINUTE)
                GROUP BY history_ledgers.sequence
            ) AS subquery
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        foreach ($queryResults as $row) {
            return $row['avg_ledger_size'];
        }
    }

}
