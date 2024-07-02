<?php

namespace App\Entity;

use App\Repository\CoinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: CoinRepository::class)]
#[Broadcast]
class Coin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $symbol = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, CoinStat>
     */
    #[ORM\OneToMany(targetEntity: CoinStat::class, mappedBy: 'coin')]
    private Collection $coinStats;

    public function __construct()
    {
        $this->coinStats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    /**
     * @return Collection<int, CoinStat>
     */
    public function getCoinStats(): Collection
    {
        return $this->coinStats;
    }

    public function addCoinStat(CoinStat $coinStat): static
    {
        if (!$this->coinStats->contains($coinStat)) {
            $this->coinStats->add($coinStat);
            $coinStat->setCoin($this);
        }

        return $this;
    }

    public function removeCoinStat(CoinStat $coinStat): static
    {
        if ($this->coinStats->removeElement($coinStat)) {
            // set the owning side to null (unless already changed)
            if ($coinStat->getCoin() === $this) {
                $coinStat->setCoin(null);
            }
        }

        return $this;
    }
}
