<?php

namespace App\Entity;

use App\Repository\LedgerStatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LedgerStatRepository::class)]
class LedgerStat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ledger_id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $lifetime = null;

    #[ORM\Column]
    private ?int $operations = null;

    #[ORM\Column]
    private ?int $successful_transactions = null;

    #[ORM\Column]
    private ?int $failed_transactions = null;

    #[ORM\Column(nullable: true)]
    private ?int $created_contracts = null;

    #[ORM\Column(nullable: true)]
    private ?int $contract_invocations = null;

    #[ORM\Column]
    private ?int $transactions_second = null;

    #[ORM\Column(nullable: true)]
    private ?int $transactions_value = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLedgerId(): ?int
    {
        return $this->ledger_id;
    }

    public function setLedgerId(int $ledger_id): static
    {
        $this->ledger_id = $ledger_id;

        return $this;
    }

    public function getLifetime(): ?string
    {
        return $this->lifetime;
    }

    public function setLifetime(string $lifetime): static
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    public function getOperations(): ?int
    {
        return $this->operations;
    }

    public function setOperations(int $operations): static
    {
        $this->operations = $operations;

        return $this;
    }

    public function getSuccessfulTransactions(): ?int
    {
        return $this->successful_transactions;
    }

    public function setSuccessfulTransactions(int $successful_transactions): static
    {
        $this->successful_transactions = $successful_transactions;

        return $this;
    }

    public function getFailedTransactions(): ?int
    {
        return $this->failed_transactions;
    }

    public function setFailedTransactions(int $failed_transactions): static
    {
        $this->failed_transactions = $failed_transactions;

        return $this;
    }

    public function getCreatedContracts(): ?int
    {
        return $this->created_contracts;
    }

    public function setCreatedContracts(?int $created_contracts): static
    {
        $this->created_contracts = $created_contracts;

        return $this;
    }

    public function getContractInvocations(): ?int
    {
        return $this->contract_invocations;
    }

    public function setContractInvocations(?int $contract_invocations): static
    {
        $this->contract_invocations = $contract_invocations;

        return $this;
    }

    public function getTransactionsSecond(): ?int
    {
        return $this->transactions_second;
    }

    public function setTransactionsSecond(int $transactions_second): static
    {
        $this->transactions_second = $transactions_second;

        return $this;
    }

    public function getTransactionsValue(): ?int
    {
        return $this->transactions_value;
    }

    public function setTransactionsValue(?int $transactions_value): static
    {
        $this->transactions_value = $transactions_value;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
