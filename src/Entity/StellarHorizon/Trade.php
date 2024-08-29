<?php

namespace App\Entity\StellarHorizon;

use App\Repository\StellarHorizon\TradeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TradeRepository::class)]
class Trade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $paging_token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $ledger_close_time = null;

    #[ORM\Column(length: 50)]
    private ?string $trade_type = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $base_offer_id = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $counter_offer_id = null;

    #[ORM\Column(length: 56, nullable: true)]
    private ?string $base_account = null;

    #[ORM\Column(length: 56, nullable: true)]
    private ?string $counter_account = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 18)]
    private ?string $base_amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 18)]
    private ?string $counter_amount = null;

    #[ORM\ManyToOne(inversedBy: 'baseTrades')]
    private ?Asset $base_asset = null;

    #[ORM\ManyToOne(inversedBy: 'counterTrades')]
    private ?Asset $counter_asset = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 18)]
    private ?string $price = null;

    #[ORM\Column]
    private ?bool $base_is_seller = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $base_liquidity_pool_id = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $counter_liquidity_pool_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPagingToken(): ?string
    {
        return $this->paging_token;
    }

    public function setPagingToken(string $paging_token): static
    {
        $this->paging_token = $paging_token;

        return $this;
    }

    public function getLedgerCloseTime(): ?\DateTimeImmutable
    {
        return $this->ledger_close_time;
    }

    public function setLedgerCloseTime(\DateTimeImmutable $ledger_close_time): static
    {
        $this->ledger_close_time = $ledger_close_time;

        return $this;
    }

    public function getTradeType(): ?string
    {
        return $this->trade_type;
    }

    public function setTradeType(string $trade_type): static
    {
        $this->trade_type = $trade_type;

        return $this;
    }

    public function getBaseOfferId(): ?string
    {
        return $this->base_offer_id;
    }

    public function setBaseOfferId(?string $base_offer_id): static
    {
        $this->base_offer_id = $base_offer_id;

        return $this;
    }

    public function getCounterOfferId(): ?string
    {
        return $this->counter_offer_id;
    }

    public function setCounterOfferId(?string $counter_offer_id): static
    {
        $this->counter_offer_id = $counter_offer_id;

        return $this;
    }

    public function getBaseAccount(): ?string
    {
        return $this->base_account;
    }

    public function setBaseAccount(?string $base_account): static
    {
        $this->base_account = $base_account;

        return $this;
    }

    public function getCounterAccount(): ?string
    {
        return $this->counter_account;
    }

    public function setCounterAccount(?string $counter_account): static
    {
        $this->counter_account = $counter_account;

        return $this;
    }

    public function getBaseAmount(): ?string
    {
        return $this->base_amount;
    }

    public function setBaseAmount(string $base_amount): static
    {
        $this->base_amount = $base_amount;

        return $this;
    }

    public function getBaseAsset(): ?Asset
    {
        return $this->base_asset;
    }

    public function setBaseAsset(?Asset $base_asset): static
    {
        $this->base_asset = $base_asset;

        return $this;
    }

    public function getCounterAmount(): ?string
    {
        return $this->counter_amount;
    }

    public function setCounterAmount(string $counter_amount): static
    {
        $this->counter_amount = $counter_amount;

        return $this;
    }

    public function getCounterAsset(): ?Asset
    {
        return $this->counter_asset;
    }

    public function setCounterAsset(?Asset $counter_asset): static
    {
        $this->counter_asset = $counter_asset;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isBaseIsSeller(): ?bool
    {
        return $this->base_is_seller;
    }

    public function setBaseIsSeller(bool $base_is_seller): static
    {
        $this->base_is_seller = $base_is_seller;

        return $this;
    }

    public function getBaseLiquidityPoolId(): ?string
    {
        return $this->base_liquidity_pool_id;
    }

    public function setBaseLiquidityPoolId(?string $base_liquidity_pool_id): static
    {
        $this->base_liquidity_pool_id = $base_liquidity_pool_id;

        return $this;
    }

    public function getCounterLiquidityPoolId(): ?string
    {
        return $this->counter_liquidity_pool_id;
    }

    public function setCounterLiquidityPoolId(?string $counter_liquidity_pool_id): static
    {
        $this->counter_liquidity_pool_id = $counter_liquidity_pool_id;

        return $this;
    }
}
