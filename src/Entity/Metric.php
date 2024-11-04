<?php

namespace App\Entity;

use App\Config\Timeframes;
use App\Repository\MetricRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MetricRepository::class)]
class Metric
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $chart_type = null;

    #[ORM\Column(length: 50)]
    private ?string $metric = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private ?string $value = null;

    #[ORM\Column(enumType: Timeframes::class)]
    private ?Timeframes $timeframe = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChartType(): ?string
    {
        return $this->chart_type;
    }

    public function setChartType(string $chart_type): static
    {
        $this->chart_type = $chart_type;

        return $this;
    }

    public function getMetric(): ?string
    {
        return $this->metric;
    }

    public function setMetric(string $metric): static
    {
        $this->metric = $metric;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getTimeframe(): ?Timeframes
    {
        return $this->timeframe;
    }

    public function setTimeframe(Timeframes $timeframe): static
    {
        $this->timeframe = $timeframe;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
