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

        foreach (Metric::cases() as $metricEnum) {
            $io->info('Processing ' . $metricEnum->label());
            $batchStartDate = new \DateTime();
            $firstMetricTimestamp = $this->aggregatedMetricsRepository->findFirstMetricTimestamp($metricEnum->value);
            if (!$firstMetricTimestamp) {
                continue;
            }
            while ($batchStartDate >= $firstMetricTimestamp) {

                $batchEndDate = (clone $batchStartDate)->sub(new \DateInterval($interval));

                $metrics = $this->aggregatedMetricsRepository->findMetricsBetweenTimestamp($metricEnum->value, $batchStartDate, $batchEndDate);

                $this->statisticsService->aggregateMetric($metrics, $timeframe, $metricEnum, $batchStartDate);
                $this->entityManager->clear();

                $batchStartDate = $batchEndDate;
            }
        }
        $io->success('Statistics builded.');

        return Command::SUCCESS;
    }
}
