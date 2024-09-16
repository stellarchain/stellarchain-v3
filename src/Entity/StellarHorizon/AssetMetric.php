<?php

namespace App\Entity\StellarHorizon;

use App\Entity\StellarHorizon\Asset;
use App\Repository\StellarHorizon\AssetMetricRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssetMetricRepository::class)]
class AssetMetric
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assetMetrics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Asset $asset = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 18)]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 18)]
    private ?string $volume_24h = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 18)]
    private ?string $circulating_supply = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $price_change_1h = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $price_change_24h = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $price_change_7d = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_trades = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): static
    {
        $this->asset = $asset;

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

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTime();
        }
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

    public function getVolume24h(): ?string
    {
        return $this->volume_24h;
    }

    public function setVolume24h(string $volume_24h): static
    {
        $this->volume_24h = $volume_24h;

        return $this;
    }

    public function getCirculatingSupply(): ?string
    {
        return $this->circulating_supply;
    }

    public function setCirculatingSupply(string $circulating_supply): static
    {
        $this->circulating_supply = $circulating_supply;

        return $this;
    }

    public function getPriceChange1h(): ?string
    {
        return $this->price_change_1h;
    }

    public function setPriceChange1h(string $price_change_1h): static
    {
        $this->price_change_1h = $price_change_1h;

        return $this;
    }

    public function getPriceChange24h(): ?string
    {
        return $this->price_change_24h;
    }

    public function setPriceChange24h(string $price_change_24h): static
    {
        $this->price_change_24h = $price_change_24h;

        return $this;
    }

    public function getPriceChange7d(): ?string
    {
        return $this->price_change_7d;
    }

    public function setPriceChange7d(string $price_change_7d): static
    {
        $this->price_change_7d = $price_change_7d;

        return $this;
    }

    public function getTotalTrades(): ?int
    {
        return $this->total_trades;
    }

    public function setTotalTrades(?int $total_trades): static
    {
        $this->total_trades = $total_trades;

        return $this;
    }
}
