<?php

namespace App\Command\Statistics;

use App\Config\Timeframes;
use App\Config\Metric;
use App\Repository\AggregatedMetricsRepository;
use App\Repository\CoinStatRepository;
use App\Repository\MetricRepository;
use App\Service\StatisticsService;
use App\Utils\Helper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'statistics:aggregate-daily',
    description: 'Build blockchain full statistics for 1 day timeframe.',
)]
class AggregateDailyStatisticsCommand extends Command
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $timeframe = Timeframes::fromString('1d');
        $interval = $this->helper->takeInterval($timeframe->label());
        $io->info($timeframe->name . " => " . $timeframe->label() . '(' . $interval . ')');

        $metricsEnum = Metric::cases();
        $batchStartDate = new \DateTime();
        $batchEndDate = (clone $batchStartDate)->sub(new \DateInterval($interval));

        foreach ($metricsEnum as $metricEnum) {
            $metrics = $this->aggregatedMetricsRepository->findMetricsBetweenTimestamp(
                $metricEnum->value,
                $batchStartDate,
                $batchEndDate
            );
            $this->statisticsService->aggregateMetric($metrics, $timeframe, $metricEnum, $batchStartDate);
        }
        $io->success('Daily statistics builded.');

        return Command::SUCCESS;
    }
}
