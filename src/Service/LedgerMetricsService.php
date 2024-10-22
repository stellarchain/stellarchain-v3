<?php

namespace App\Service;

use App\Repository\LedgerStatRepository;

class LedgerMetricsService
{
    public function __construct(public LedgerStatRepository $ledgerRepository)
    {
    }

    public function getDailyMetrics(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $ledgers = $this->ledgerRepository->getBetweenDates($startDate, $endDate);

        $ledgersPerDay = count($ledgers);
        $transactionsPerLedger = 0;
        $operationsPerLedger = 0;
        $numberOfTransactions = 0;
        $numberOfOperations = 0;
        $totalTime = 0;
        $successfulTransactions = 0;
        $failedTransactions = 0;
        $contractInvocations = 0;
        $createdContracts = 0;

        foreach ($ledgers as $ledger) {
            $transactionsPerLedger += $ledger->getSuccessfulTransactions() + $ledger->getFailedTransactions();
            $operationsPerLedger += $ledger->getOperations();
            $numberOfTransactions += $ledger->getSuccessfulTransactions() + $ledger->getFailedTransactions();
            $numberOfOperations += $ledger->getOperations();
            $totalTime += $ledger->getLifetime(); // Assuming 'lifetime' is the ledger time
            $successfulTransactions += $ledger->getSuccessfulTransactions();
            $failedTransactions += $ledger->getFailedTransactions();
            $contractInvocations += $ledger->getContractInvocations();
            $createdContracts += $ledger->getCreatedContracts();
        }

        $averageLedgerTime = $ledgersPerDay > 0 ? $totalTime / $ledgersPerDay : 0;
        $transactionsPerSecond = $totalTime > 0 ? $numberOfTransactions / $totalTime : 0;

        return [
            'ledgers_per_day' => $ledgersPerDay,
            'transactions_per_ledger' => $ledgersPerDay > 0 ? $transactionsPerLedger / $ledgersPerDay : 0,
            'operations_per_ledger' => $ledgersPerDay > 0 ? $operationsPerLedger / $ledgersPerDay : 0,
            'number_of_transactions' => $numberOfTransactions,
            'number_of_operations' => $numberOfOperations,
            'average_ledger_time' => $averageLedgerTime,
            'successful_transactions' => $successfulTransactions,
            'failed_transactions' => $failedTransactions,
            'transactions_per_second' => $transactionsPerSecond,
            'contract_invocations' => $contractInvocations,
            'created_contracts' => $createdContracts,
        ];
    }


    public function getMetricsForTimeIntervals(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        int $page,
        int $perPage
    ): array {
        $interval = new \DateInterval('PT10M'); // 10-minute interval
        $periods = new \DatePeriod($startDate, $interval, $endDate);

        // Calculate the offset and limit based on the current page
        $offset = ($page - 1) * $perPage;

        // Convert the periods to an array for pagination (avoid iterating the entire day at once)
        $periodArray = iterator_to_array($periods);
        $pagedPeriods = array_slice($periodArray, $offset, $perPage);

        $metricsByInterval = [];

        foreach ($pagedPeriods as $periodStart) {
            $periodEnd = $periodStart->add($interval);

            // Get ledgers for this specific 10-minute period
            $ledgers = $this->ledgerRepository->getBetweenDates($periodStart, $periodEnd);

            $ledgersPerInterval = count($ledgers);
            $transactionsPerLedger = 0;
            $operationsPerLedger = 0;
            $numberOfTransactions = 0;
            $numberOfOperations = 0;
            $totalTime = 0;
            $successfulTransactions = 0;
            $failedTransactions = 0;
            $contractInvocations = 0;
            $createdContracts = 0;

            if ($ledgersPerInterval) {
                foreach ($ledgers as $ledger) {
                    $transactionsPerLedger += $ledger->getSuccessfulTransactions() + $ledger->getFailedTransactions();
                    $operationsPerLedger += $ledger->getOperations();
                    $numberOfTransactions += $ledger->getSuccessfulTransactions() + $ledger->getFailedTransactions();
                    $numberOfOperations += $ledger->getOperations();
                    $totalTime += $ledger->getLifetime();
                    $successfulTransactions += $ledger->getSuccessfulTransactions();
                    $failedTransactions += $ledger->getFailedTransactions();
                    $contractInvocations += $ledger->getContractInvocations();
                    $createdContracts += $ledger->getCreatedContracts();
                }

                $averageLedgerTime = $ledgersPerInterval > 0 ? $totalTime / $ledgersPerInterval : 0;
                $transactionsPerSecond = $totalTime > 0 ? $numberOfTransactions / $totalTime : 0;

                $metricsByInterval[] = [
                    'time_start' => $periodStart->format('m-d-Y H:i'),
                    'ledgers_per_interval' => $ledgersPerInterval,
                    'transactions_per_ledger' => $ledgersPerInterval > 0 ? $transactionsPerLedger / $ledgersPerInterval : 0,
                    'operations_per_ledger' => $ledgersPerInterval > 0 ? $operationsPerLedger / $ledgersPerInterval : 0,
                    'number_of_transactions' => $numberOfTransactions,
                    'number_of_operations' => $numberOfOperations,
                    'average_ledger_time' => $averageLedgerTime,
                    'successful_transactions' => $successfulTransactions,
                    'failed_transactions' => $failedTransactions,
                    'transactions_per_second' => $transactionsPerSecond,
                    'contract_invocations' => $contractInvocations,
                    'created_contracts' => $createdContracts,
                ];
            }
        }
        return $metricsByInterval;
    }
}
