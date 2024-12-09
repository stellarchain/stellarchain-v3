<?php

namespace App\Entity\Horizon;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\Horizon\OffersRepository;

#[ORM\Entity(repositoryClass: OffersRepository::class)]
class Offers
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $seller_id;

    #[ORM\Column(type: 'integer')]
    private int $offer_id;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private int $selling_asset;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private int $buying_asset;

    #[ORM\Column(type: 'integer')]
    private int $amount;

    #[ORM\Column(type: 'integer')]
    private int $pricen;

    #[ORM\Column(type: 'integer')]
    private int $priced;

    #[ORM\Column(type: 'numeric')]
    private int $price;

    #[ORM\Column(type: 'integer')]
    private int $flags;

    public function getSellerId(): string
    {
        return $this->seller_id;
    }

    public function getOfferId(): int
    {
        return $this->offer_id;
    }

    public function getSellingAsset(): ?int
    {
        return $this->selling_asset;
    }

    public function getBuyingAsset(): ?int
    {
        return $this->buying_asset;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPricen(): int
    {
        return $this->pricen;
    }

    public function getPriced(): int
    {
        return $this->priced;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }
}
