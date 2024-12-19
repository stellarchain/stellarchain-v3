<?php

namespace App\Entity;

use App\Config\Metric as MetricEnum;
use App\Config\Timeframes;
use App\Repository\AggregatedMetricsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AggregatedMetricsRepository::class)]
class AggregatedMetrics
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: MetricEnum::class)]
    private ?MetricEnum $metric_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_entries = null;

    #[ORM\Column(length: 50)]
    private ?string $total_value = null;

    #[ORM\Column(length: 50)]
    private ?string $avg_value = null;

    #[ORM\Column(length: 50)]
    private ?string $max_value = null;

    #[ORM\Column(length: 50)]
    private ?int $min_value = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(enumType: Timeframes::class)]
    private ?Timeframes $timeframe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMetricId(): ?MetricEnum
    {
        return $this->metric_id;
    }

    public function setMetricId(MetricEnum $metric_id): static
    {
        $this->metric_id = $metric_id;

        return $this;
    }

    public function getTotalEntries(): ?int
    {
        return $this->total_entries;
    }

    public function setTotalEntries(?int $total_entries): static
    {
        $this->total_entries = $total_entries;

        return $this;
    }

    public function getTotalValue(): ?string
    {
        return $this->total_value;
    }

    public function setTotalValue(?string $total_value): static
    {
        $this->total_value = $total_value;

        return $this;
    }

    public function getAvgValue(): ?string
    {
        return $this->avg_value;
    }

    public function setAvgValue(?string $avg_value): static
    {
        $this->avg_value = $avg_value;

        return $this;
    }

    public function getMaxValue(): ?string
    {
        return $this->max_value;
    }

    public function setMaxValue(?string $max_value): static
    {
        $this->max_value = $max_value;

        return $this;
    }

    public function getMinValue(): ?int
    {
        return $this->min_value;
    }

    public function setMinValue(?int $min_value): static
    {
        $this->min_value = $min_value;

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

    public function getTimeframe(): ?Timeframes
    {
        return $this->timeframe;
    }

    public function setTimeframe(Timeframes $timeframe): static
    {
        $this->timeframe = $timeframe;

        return $this;
    }
}
