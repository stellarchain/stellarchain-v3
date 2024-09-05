<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class TransactionBatchService
{
    private int $totalSuccessfulTransactions = 0;
    private int $totalFailedTransactions = 0;
    private int $totalContractInvocations = 0;
    private int $totalContractCreated = 0;
    private int $totalOperations = 0;
    private int $ledgerId = 0;
    private int $transactionCount = 0;
    private ?\DateTimeImmutable $lastLedgerChangeTime = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function increaseTotalSuccessfulTransactions(): void
    {
        $this->totalSuccessfulTransactions++;
        $this->transactionCount++;
    }

    public function increaseTotalFailedTransactions(): void
    {
        $this->totalFailedTransactions++;
        $this->transactionCount++;
    }

    public function increaseTotalContractCreated(): void
    {
        $this->totalContractCreated++;
    }

    public function increaseTotalOperations(int $totalOps): void
    {
        $this->totalOperations += $totalOps;
    }

    public function increaseTotalContractInvocations(): void
    {
        $this->totalContractInvocations++;
    }

    public function setLedgerId(int $ledgerId): void {
        $this->ledgerId = $ledgerId;
    }

    public function getLedgerId(): int
    {
        return $this->ledgerId;
    }

    public function updateLedgerId(int $ledgerId): void
    {
        if ($this->ledgerId !== $ledgerId) {
            $currentTime = new \DateTimeImmutable();
            $transactionsPerSecond = 0;
            $timePassed = 0;
            if ($this->lastLedgerChangeTime !== null) {
                $timePassed = $currentTime->getTimestamp() - $this->lastLedgerChangeTime->getTimestamp();
                $transactionsPerSecond = $timePassed > 0 ? $this->transactionCount / $timePassed : 0;
            }

            dump(
                'Ledger Id: ' . $this->ledgerId,
                'Ledger Time: '. $timePassed . ' seconds',
                'Operations Count: ' . $this->totalOperations,
                'Transactions success: ' . $this->totalSuccessfulTransactions,
                'Transactions failed:' . $this->totalFailedTransactions,
                'Created Contracts: ' . $this->totalContractCreated,
                'Invoke Contracts: ' . $this->totalContractInvocations,
                'Transactions/second: ' . $transactionsPerSecond,
                'Transactions Value'
            );

            //$this->entityManager->flush();
            //$this->entityManager->clear();

            $this->resetCounters();
            // Update the ledger ID
            $this->ledgerId = $ledgerId;
            $this->lastLedgerChangeTime = $currentTime;
        }
    }

    private function resetCounters(): void
    {
        $this->totalSuccessfulTransactions = 0;
        $this->totalFailedTransactions = 0;
        $this->totalContractInvocations = 0;
        $this->totalContractCreated = 0;
        $this->totalOperations = 0;
        $this->transactionCount = 0;
    }
}
