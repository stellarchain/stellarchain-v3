<?php

namespace App\Command;

use App\Config\Timeframes;
use App\Entity\Horizon\HistoryTransactions;
use App\Entity\Metric;
use App\Repository\CoinStatRepository;
use App\Repository\Horizon\HistoryTransactionsRepository;
use App\Service\StatisticsService;
use App\Service\LedgerMetricsService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Persistence\ManagerRegistry;

#[AsCommand(
    name: 'statistics:build',
    description: 'Add a short description for your command',
)]
class StatisticsBuildCommand extends Command
{
    public function __construct(
        private CoinStatRepository $coinStatRepository,
        private ManagerRegistry $doctrine,
        private StatisticsService $statisticsService,
        private LedgerMetricsService $ledgerMetricsService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('timeframe', InputArgument::REQUIRED, 'Timeframe statistics.(10m)');
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

        $io->info($timeframe->name . " => " . $timeframe->label() . '(' . $interval . ')');

        $this->buildStatistics($timeframe->label(), $interval);

        $io->success('Statistics builded.');

        return Command::SUCCESS;
    }

    private function getTotalOutput()
    {
        /* $customers = $this->doctrine->getRepository(HistoryTransactions::class, 'horizon'); */
        /* $history = $customers->findOneBy(['id' => 164908000931225600]); */
        $startDate = $this->getFirstLedgerTimestamp();
        $endDate = new \DateTime();
        $interval = new \DateInterval('PT15M');

        $currentBatchStart = clone $startDate;
        while ($currentBatchStart < $endDate) {
            $batchEnd = (clone $currentBatchStart)->add($interval);

            $this->processBatch($currentBatchStart, $batchEnd);
            $currentBatchStart = $this->getNextBatchStart($batchEnd);

            if (!$currentBatchStart) {
                break;
            }
        }

        return Command::SUCCESS;
    }

    private function getFirstLedgerTimestamp()
    {
        $sql = "SELECT MIN(closed_at) AS first_timestamp FROM public.history_ledgers";
        $conn = $this->doctrine->getConnection('horizon');
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return new \DateTime($result->fetchOne());
    }

    private function getNextBatchStart(\DateTime $after): ?\DateTime
    {
        $sql = "SELECT MIN(closed_at) AS next_timestamp
                FROM public.history_ledgers
        WHERE closed_at > :after_time";

        $result = $this->doctrine->getConnection('horizon')
            ->fetchOne($sql, ['after_time' => $after->format('Y-m-d H:i:s')]);

        return $result ? new \DateTime($result) : null;
    }

    private function processBatch(\DateTime $start, \DateTime $end): void
    {
        $sql = "
            SELECT id FROM public.history_transactions
            WHERE public.history_transactions.ledger_sequence
            IN (
                SELECT sequence
                FROM public.history_ledgers
                WHERE public.history_ledgers.closed_at >= :start_time
                    AND public.history_ledgers.closed_at < :end_time
            )
        ";
        $params = [
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time' => $end->format('Y-m-d H:i:s'),
        ];
        $result = $this->doctrine->getConnection('horizon')->fetchAllAssociative($sql, $params);
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
        /*     'market-charts', */
        /*     'total-trades', */
        /*     $this->stellarBigQuery->dexTrades() */
        /* ); */

        /* $this->buildMetric( */
        /*     $timeframe, */
        /*     'blockchain-charts', */
        /*     'blockchain-size', */
        /*     $this->stellarBigQuery->getBlockchainSize() */
        /* ); */

        /* $this->buildMetric( */
        /*     $timeframe, */
        /*     'networks-charts', */
        /*     'total-assets', */
        /*     $this->stellarBigQuery->totalAssets() */
        /* ); */

        /* $this->buildMetric( */
        /*     $timeframe, */
        /*     'networks-charts', */
        /*     'total-accounts', */
        /*     $this->stellarBigQuery->totalAccounts() */
        /* ); */

        /* $this->buildMetric( */
        /*     $timeframe, */
        /*     'networks-charts', */
        /*     'active-addresses', */
        /*     $this->stellarBigQuery->activeAddressesCount() */
        /* ); */

        /* $this->buildMetric( */
        /*     $timeframe, */
        /*     'networks-charts', */
        /*     'inactive-addresses', */
        /*     $this->stellarBigQuery->inactiveAddressesCount() */
        /* ); */

        /* $this->buildMetric( */
        /*     $timeframe, */
        /*     'networks-charts', */
        /*     'top-100-active-addresses', */
        /*     $this->stellarBigQuery->top100ActiveAddressesAvgBalance() */
        /* ); */


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
