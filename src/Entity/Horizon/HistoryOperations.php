<?php

namespace App\Entity\Horizon;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Horizon\HistoryOperationsRepository;

#[ORM\Entity(repositoryClass: HistoryOperationsRepository::class)]
class HistoryOperations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private $id;

    #[ORM\Column(type: 'integer')]
    private int $transaction_id;

    #[ORM\Column(type: 'integer')]
    private int $type;

    #[ORM\Column(type: 'json')]
    private array $details;

    #[ORM\Column(type: 'bool')]
    private array $is_payment;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTransactionId(): int
    {
        return $this->transaction_id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getIsPayment(): array
    {
        return $this->is_payment;
    }
}
