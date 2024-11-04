<?php

namespace App\Service;

use Google\Cloud\BigQuery\BigQueryClient;

class StellarBigQuery
{

    protected BigQueryClient $bigQuery;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient(['keyFile' => json_decode(file_get_contents(env('GOOGLE_CLOUD_KEY_FILE')), true)]);
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
                WHERE ledger_closed_at > TIMESTAMP_SUB(CURRENT_TIMESTAMP(), INTERVAL 24 HOUR)";

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
        foreach($queryResults as $row){
            return $row['size'];
        }
    }
}
