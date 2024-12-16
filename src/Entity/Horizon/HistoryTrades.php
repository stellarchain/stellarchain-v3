<?php

namespace App\Entity\Horizon;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Horizon\HistoryTradesRepository;

#[ORM\Entity(repositoryClass: HistoryTradesRepository::class)]
class HistoryTrades
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private string $history_operation_id;

    #[ORM\Column(type: 'integer')]
    private int $base_account_id;

    #[ORM\Column]
    private ?\DateTimeImmutable $ledger_closed_at = null;

    #[ORM\Column(type: 'integer')]
    private int $order;

    #[ORM\Column(type: 'integer')]
    private int $base_asset_id;

    #[ORM\Column(type: 'integer')]
    private int $base_amount;

    #[ORM\Column(type: 'integer')]
    private int $counter_account_id;

    #[ORM\Column(type: 'integer')]
    private int $counter_asset_id;

    #[ORM\Column(type: 'integer')]
    private int $counter_amount;

    #[ORM\Column(type: 'boolean')]
    private int $base_is_seller;

    #[ORM\Column(type: 'integer')]
    private int $price_n;

    #[ORM\Column(type: 'integer')]
    private int $price_d;

    #[ORM\Column(type: 'integer')]
    private int $base_offer_id;

    #[ORM\Column(type: 'integer')]
    private int $counter_offer_id;

    #[ORM\Column(type: 'integer')]
    private int $trade_type;

    #[ORM\Column(type: 'integer')]
    private int $base_is_exact;

    public function getHistoryOperationId(): string
    {
        return $this->history_operation_id;
    }

    public function getLedgerClosedAt(): ?\DateTimeImmutable
    {
        return $this->ledger_closed_at;
    }

    public function getBaseAccountId(): int
    {
        return $this->base_account_id;
    }

    public function getBaseAssetId(): int
    {
        return $this->base_asset_id;
    }

    public function getBaseAmount(): int
    {
        return $this->base_amount;
    }

    public function getCounterAccountId(): int
    {
        return $this->counter_account_id;
    }

    public function getCounterAssetId(): int
    {
        return $this->counter_asset_id;
    }

    public function getCounterAmount(): int
    {
        return $this->counter_amount;
    }

    public function isBaseSeller(): bool
    {
        return $this->base_is_seller;
    }

    public function getPriceN(): int
    {
        return $this->price_n;
    }

    public function getPriceD(): int
    {
        return $this->price_d;
    }

    public function getBaseOfferId(): int
    {
        return $this->base_offer_id;
    }

    public function getCounterOfferId(): int
    {
        return $this->counter_offer_id;
    }

    public function getTradeType(): int
    {
        return $this->trade_type;
    }

    public function isBaseExact(): int
    {
        return $this->base_is_exact;
    }
}
