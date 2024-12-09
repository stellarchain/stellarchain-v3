<?php

namespace App\Entity\Horizon;

use App\Repository\Horizon\HistoryAssetsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoryAssetsRepository::class)]
class HistoryAssets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $asset_type;

    #[ORM\Column(type: 'string')]
    private int $asset_code;

    #[ORM\Column(type: 'string')]
    private int $asset_issuer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssetType(): int
    {
        return $this->asset_type;
    }

    public function getAssetCode(): string
    {
        return $this->asset_code;
    }

    public function getAssetIssuer(): string
    {
        return $this->asset_issuer;
    }
}
