<?php

namespace App\Command\Statistics;

use App\Config\Timeframes;
use App\Config\Metric;
use App\Entity\AggregatedMetrics;
use App\Repository\AggregatedMetricsRepository;
use App\Repository\CoinStatRepository;
use App\Repository\MetricRepository;
use App\Service\StatisticsService;
use App\Utils\Helper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'statistics:aggregate-timeframes',
    description: 'Build blockchain full statistics for each timeframe.',
)]
class AggregateStatisticsCommand extends Command
{
    public function __construct(
        private CoinStatRepository $coinStatRepository,
        private StatisticsService $statisticsService,
        private MetricRepository $metricRepository,
        private AggregatedMetricsRepository $aggregatedMetricsRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private Helper $helper
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('timeframe', InputArgument::REQUIRED, 'Timeframe statistics.(1d)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $timeframe = Timeframes::fromString($input->getArgument('timeframe'));
        if (!$timeframe) {
            $io->error('Timeframe not supported');
            return Command::FAILURE;
        }

        $interval = $this->helper->takeInterval($timeframe->label());
        if (!$interval) {
            $io->error('Timeframe not supported');
            return Command::FAILURE;
        }
        $io->info($timeframe->name . " => " . $timeframe->label() . '(' . $interval . ')');

        $metricsEnum = Metric::cases();
        $batchStartDate = new \DateTime();

        foreach ($metricsEnum as $metricEnum) {
            $firstMetricTimestamp = $this->metricRepository->findFirstMetricTimestamp($metricEnum->label());
            while ($batchStartDate >= $firstMetricTimestamp) {
                $batchEndDate = (clone $batchStartDate)->sub(new \DateInterval($interval));

                $metrics = $this->metricRepository->findMetricsBetweenTimestamp($metricEnum->label(), $batchStartDate, $batchEndDate);

                $this->aggregateMetric($metrics, $timeframe, $metricEnum, $batchStartDate);
                $batchStartDate = $batchEndDate;
            }
        }
        $io->success('Statistics builded.');

        return Command::SUCCESS;
    }

    public function aggregateMetric($metrics, $timeframe, $metricEnum, \DateTime $batchStartDate)
    {
        if (empty($metrics)) {
            return;
        }

        $totalEntries = 0;
        $totalValue = 0;
        $minValue = PHP_INT_MAX;
        $maxValue = -PHP_INT_MAX;
        $sumValue = 0;

        foreach ($metrics as $metric) {
            $totalEntries++;
            $metricValue = (float) $metric->getValue();

            $totalValue += $metricValue;
            $sumValue += $metricValue;

            if ($metricValue < $minValue) {
                $minValue = $metricValue;
            }

            if ($metricValue > $maxValue) {
                $maxValue = $metricValue;
            }
        }

        $avgValue = $totalEntries > 0 ? $sumValue / $totalEntries : 0;

        $batchStartDateImmutable = \DateTimeImmutable::createFromMutable($batchStartDate);
        $aggregateMetric = new AggregatedMetrics();
        $aggregateMetric
            ->setTotalEntries($totalEntries)
            ->setMetricId($metricEnum)
            ->setTotalValue($totalValue)
            ->setAvgValue($avgValue)
            ->setMaxValue($maxValue)
            ->setMinValue($minValue)
            ->setCreatedAt($batchStartDateImmutable)
            ->setTimeframe($timeframe);

        $this->entityManager->persist($aggregateMetric);
        $this->entityManager->flush();
    }
}
