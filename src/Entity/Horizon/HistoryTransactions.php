<?php

namespace App\Entity\Horizon;

use App\Repository\Horizon\HistoryTransactionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoryTransactionsRepository::class)]
class HistoryTransactions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $transaction_hash = null;

    #[ORM\Column]
    private ?int $ledger_sequence = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionHash(): ?string
    {
        return $this->transaction_hash;
    }

    public function setTransactionHash(string $transaction_hash): static
    {
        $this->transaction_hash = $transaction_hash;

        return $this;
    }

    public function getLedgerSequence(): ?int
    {
        return $this->ledger_sequence;
    }

    public function setLedgerSequence(int $ledger_sequence): static
    {
        $this->ledger_sequence = $ledger_sequence;

        return $this;
    }
}
