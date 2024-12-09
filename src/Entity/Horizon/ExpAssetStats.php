<?php

namespace App\Entity\Horizon;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Horizon\ExpAssetStatsRepository;

#[ORM\Entity(repositoryClass: ExpAssetStatsRepository::class)]
#[ORM\UniqueConstraint(columns: ["asset_type", "asset_code", "asset_issuer"])]
class ExpAssetStats
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private string $asset_type;

    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private int $asset_code;

    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private int $asset_issuer;

    #[ORM\Column(type: 'json')]
    private int $accounts;

    #[ORM\Column(type: 'json')]
    private int $balances;

    #[ORM\Column(type: 'binary')]
    private int $contract_id;

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

    public function getAccounts()
    {
        return $this->accounts;
    }

    public function getBalances()
    {
        return $this->balances;
    }

    public function getContractId(): int
    {
        return $this->contract_id;
    }
}
