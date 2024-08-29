<?php

namespace App\Entity\StellarHorizon;

use App\Entity\StellarHorizon\AssetMetric;
use App\Repository\StellarHorizon\AssetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[UniqueEntity(
    fields: ['asset_type', 'asset_code', 'asset_issuer'],
    ignoreNull: ['asset_code', 'asset_issuer'],
    message: 'This asset already exists.',
)]
class Asset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $asset_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $asset_code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $asset_issuer = null;

    #[ORM\Column(nullable: true)]
    private array $accounts = [];

    #[ORM\Column(nullable: true)]
    private ?int $num_claimable_balances = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_contracts = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_liquidity_pools = null;

    #[ORM\Column(nullable: true)]
    private array $balances = [];

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $claimable_balances_amount = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $contracts_amount = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $liquidity_pools_amount = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_accounts = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_archived_contracts = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $archived_contracts_amount = null;

    #[ORM\Column(nullable: true)]
    private array $flags = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\OneToMany(targetEntity: Trade::class, mappedBy: 'base_asset')]
    private Collection $baseTrades;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\OneToMany(targetEntity: Trade::class, mappedBy: 'base_asset')]
    private Collection $counterTrades;

    #[ORM\Column(nullable: true)]
    private ?bool $in_market = false;

    /**
     * @var Collection<int, AssetMetric>
     */
    #[ORM\OneToMany(targetEntity: AssetMetric::class, mappedBy: 'asset')]
    private Collection $assetMetrics;

    public function __construct()
    {
        $this->updated_at = new \DateTimeImmutable();
        $this->created_at = new \DateTimeImmutable();
        $this->baseTrades = new ArrayCollection();
        $this->counterTrades = new ArrayCollection();
        $this->assetMetrics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssetType(): ?string
    {
        return $this->asset_type;
    }

    public function setAssetType(string $asset_type): static
    {
        $this->asset_type = $asset_type;

        return $this;
    }

    public function getAssetCode(): ?string
    {
        return $this->asset_code;
    }

    public function setAssetCode(?string $asset_code): static
    {
        $this->asset_code = $asset_code;

        return $this;
    }

    public function getAssetIssuer(): ?string
    {
        return $this->asset_issuer;
    }

    public function setAssetIssuer(?string $asset_issuer): static
    {
        $this->asset_issuer = $asset_issuer;

        return $this;
    }

    public function getAccounts(): array
    {
        return $this->accounts;
    }

    public function setAccounts(array $accounts): static
    {
        $this->accounts = $accounts;

        return $this;
    }

    public function getNumClaimableBalances(): ?int
    {
        return $this->num_claimable_balances;
    }

    public function setNumClaimableBalances(int $num_claimable_balances): static
    {
        $this->num_claimable_balances = $num_claimable_balances;

        return $this;
    }

    public function getNumContracts(): ?int
    {
        return $this->num_contracts;
    }

    public function setNumContracts(int $num_contracts): static
    {
        $this->num_contracts = $num_contracts;

        return $this;
    }

    public function getNumLiquidityPools(): ?int
    {
        return $this->num_liquidity_pools;
    }

    public function setNumLiquidityPools(int $num_liquidity_pools): static
    {
        $this->num_liquidity_pools = $num_liquidity_pools;

        return $this;
    }

    public function getBalances(): array
    {
        return $this->balances;
    }

    public function setBalances(array $balances): static
    {
        $this->balances = $balances;

        return $this;
    }

    public function getClaimableBalancesAmount(): ?string
    {
        return $this->claimable_balances_amount;
    }

    public function setClaimableBalancesAmount(string $claimable_balances_amount): static
    {
        $this->claimable_balances_amount = $claimable_balances_amount;

        return $this;
    }

    public function getContractsAmount(): ?string
    {
        return $this->contracts_amount;
    }

    public function setContractsAmount(string $contracts_amount): static
    {
        $this->contracts_amount = $contracts_amount;

        return $this;
    }

    public function getLiquidityPoolsAmount(): ?string
    {
        return $this->liquidity_pools_amount;
    }

    public function setLiquidityPoolsAmount(string $liquidity_pools_amount): static
    {
        $this->liquidity_pools_amount = $liquidity_pools_amount;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getNumAccounts(): ?int
    {
        return $this->num_accounts;
    }

    public function setNumAccounts(int $num_accounts): static
    {
        $this->num_accounts = $num_accounts;

        return $this;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function setFlags(array $flags): static
    {
        $this->flags = $flags;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

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

    public function getNumArchivedContracts(): ?int
    {
        return $this->num_archived_contracts;
    }

    public function setNumArchivedContracts(int $num_archived_contracts): static
    {
        $this->num_archived_contracts = $num_archived_contracts;

        return $this;
    }

    public function getArchivedContractsAmount(): ?string
    {
        return $this->archived_contracts_amount;
    }

    public function setArchivedContractsAmount(string $archived_contracts_amount): static
    {
        $this->archived_contracts_amount = $archived_contracts_amount;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTime();
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @return Collection<int, Trade>
     */
    public function getBaseTrades(): Collection
    {
        return $this->baseTrades;
    }

    public function addBaseTrade(Trade $trade): static
    {
        if (!$this->baseTrades->contains($trade)) {
            $this->baseTrades->add($trade);
            $trade->setBaseAsset($this);
        }

        return $this;
    }

    public function removeBaseTrade(Trade $trade): static
    {
        if ($this->baseTrades->removeElement($trade)) {
            // set the owning side to null (unless already changed)
            if ($trade->getBaseAsset() === $this) {
                $trade->setBaseAsset(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trade>
     */
    public function getCounterTrades(): Collection
    {
        return $this->counterTrades;
    }

    public function addCounterTrade(Trade $trade): static
    {
        if (!$this->counterTrades->contains($trade)) {
            $this->counterTrades->add($trade);
            $trade->setCounterAsset($this);
        }

        return $this;
    }

    public function removeCounterTrade(Trade $trade): static
    {
        if ($this->counterTrades->removeElement($trade)) {
            // set the owning side to null (unless already changed)
            if ($trade->getCounterAsset() === $this) {
                $trade->setCounterAsset(null);
            }
        }

        return $this;
    }

    public function isInMarket(): ?bool
    {
        return $this->in_market;
    }

    public function setInMarket(bool $in_market): static
    {
        $this->in_market = $in_market;

        return $this;
    }

    /**
     * @return Collection<int, AssetMetric>
     */
    public function getAssetMetrics(): Collection
    {
        return $this->assetMetrics;
    }

    public function addAssetMetric(AssetMetric $assetMetric): static
    {
        if (!$this->assetMetrics->contains($assetMetric)) {
            $this->assetMetrics->add($assetMetric);
            $assetMetric->setAsset($this);
        }

        return $this;
    }

    public function removeAssetMetric(AssetMetric $assetMetric): static
    {
        if ($this->assetMetrics->removeElement($assetMetric)) {
            // set the owning side to null (unless already changed)
            if ($assetMetric->getAsset() === $this) {
                $assetMetric->setAsset(null);
            }
        }

        return $this;
    }

}
