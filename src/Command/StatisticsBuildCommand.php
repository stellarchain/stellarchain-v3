<?php

namespace App\Command;

use App\Config\Timeframes;
use App\Entity\Metric;
use App\Repository\CoinStatRepository;
use App\Service\StatisticsService;
use App\Service\LedgerMetricsService;
use App\Service\StellarBigQuery;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'statistics:build',
    description: 'Add a short description for your command',
)]
class StatisticsBuildCommand extends Command
{
    public function __construct(
        private CoinStatRepository $coinStatRepository,
        private StatisticsService $statisticsService,
        private StellarBigQuery $stellarBigQuery,
        private LedgerMetricsService $ledgerMetricsService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('timeframe', InputArgument::REQUIRED, 'Timeframe statistics.10m');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $timeframe = Timeframes::fromString($input->getArgument('timeframe'));
        if (!$timeframe) {
            $io->error('Timeframe not supported');
            return Command::FAILURE;
        }

        $interval = $this->takeInterval($timeframe->label());
        if (!$interval) {
            $io->error('Timeframe not supported');
            return Command::FAILURE;
        }

        $io->info($timeframe->name . " => " . $timeframe->label() .'('.$interval.')');

        $this->buildStatistics($timeframe->label(), $interval);

        $io->success('Statistics builded.');

        return Command::SUCCESS;
    }

    /**
     * @return void
     */
    public function buildStatistics($timeframe, $interval): void
    {
        $endDate = new \DateTimeImmutable();
        $startDate = $endDate->sub(new \DateInterval($interval));

        $blockchainMetrics  = $this->ledgerMetricsService->getMetrics($startDate, $endDate);

        foreach ($blockchainMetrics as $bMetric => $bMetricValue) {
            $this->buildMetric(
                $timeframe,
                'blockchain-charts',
                str_replace('_', '-', $bMetric),
                $bMetricValue
            );
        }

        $this->buildMetric(
            $timeframe,
            'market-charts',
            'total-trades',
            $this->stellarBigQuery->dexTrades()
        );

        $this->buildMetric(
            $timeframe,
            'blockchain-charts',
            'blockchain-size',
            $this->stellarBigQuery->getBlockchainSize()
        );

        $this->buildMetric(
            $timeframe,
            'networks-charts',
            'total-assets',
            $this->stellarBigQuery->totalAssets()
        );

        $this->buildMetric(
            $timeframe,
            'networks-charts',
            'total-accounts',
            $this->stellarBigQuery->totalAccounts()
        );

        $this->buildMetric(
            $timeframe,
            'networks-charts',
            'active-addresses',
            $this->stellarBigQuery->activeAddressesCount()
        );

        $this->buildMetric(
            $timeframe,
            'networks-charts',
            'inactive-addresses',
            $this->stellarBigQuery->inactiveAddressesCount()
        );

        $this->buildMetric(
            $timeframe,
            'networks-charts',
            'top-100-active-addresses',
            $this->stellarBigQuery->top100ActiveAddressesAvgBalance()
        );

        $this->buildMetric(
            $timeframe,
            'networks-charts',
            'successful-transactions',
            $blockchainMetrics['successful_transactions']
        );

        $this->buildMetric(
            $timeframe,
            'networks-charts',
            'successful-transactions',
            $blockchainMetrics['failed_transactions']
        );

        /* $this->buildMetric( */
        /*     $timeframe, */
        /*     'blockchain-charts', */
        /*     'average-ledger-size', */
        /*     $this->stellarBigQuery->averageLedgerSizeLast5Minutes() */
        /* ); */

        /* 'output-value-per-day' => [], */
        /* 'transactions-value' => [], */
    }

    public function buildMetric($timeframe, $chartType, $key, $value): void
    {
        $metric = new Metric();
        $metric->setChartType($chartType)
            ->setTimeframe(Timeframes::fromString($timeframe))
            ->setValue($value)
            ->setTimestamp(new DateTimeImmutable())
            ->setMetric($key);

        $this->entityManager->persist($metric);
        $this->entityManager->flush();
    }

    public function takeInterval(string $label): string
    {
        $interval = strtoupper($label);
        if (strpos($interval, 'D') !== false) {
            $interval = 'P' . str_replace('D', 'D', $interval);
        } elseif (strpos($interval, 'H') !== false) {
            $interval = 'PT' . str_replace('H', 'H', $interval);
        } elseif (strpos($interval, 'M') !== false) {
            $interval = 'PT' . str_replace('M', 'M', $interval);
        } else {
            return false;
        }

        return $interval;
    }
}
