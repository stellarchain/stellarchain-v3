<?php

namespace App\Entity;

use App\Repository\CoinStatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: CoinStatRepository::class)]
class CoinStat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $value = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'coinStats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coin $coin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoin(): ?Coin
    {
        return $this->coin;
    }

    public function setCoin(?Coin $coin): static
    {
        $this->coin = $coin;

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

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;

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

    public function updateTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        if ($this->getId() === null) {
            $this->setCreatedAt($now);
        }
    }
}
