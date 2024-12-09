<?php
namespace App\Entity\Horizon;

use App\Repository\Horizon\HistoryLedgersRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoryLedgersRepository::class)]
class HistoryLedgers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $sequence = null;

    #[ORM\Column]
    private ?int $transaction_count = null;

    #[ORM\Column]
    private ?int $operation_count = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $closed_at = null;

    #[ORM\Column]
    private ?int $total_coins = null;

    #[ORM\Column]
    private ?int $successful_transaction_count = null;

    #[ORM\Column]
    private ?int $failed_transaction_count = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function getSuccessfulTransactionCount(): ?int
    {
        return $this->successful_transaction_count;
    }

    public function getTransactionCount(): ?int
    {
        return $this->transaction_count;
    }

    public function getFailedTransactionCount(): ?int
    {
        return $this->failed_transaction_count;
    }

    public function getTotalCoins(): ?int
    {
        return $this->total_coins;
    }

    public function getOperationCount(): ?int
    {
        return $this->operation_count;
    }

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closed_at;
    }
}
