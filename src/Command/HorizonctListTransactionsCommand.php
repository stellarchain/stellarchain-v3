<?php

namespace App\Command;

use App\Config\Timeframes;
use App\Entity\Metric;
use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\ListTransactions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\Horizon\HistoryTransactionsRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Horizon\HistoryTransactions;

#[AsCommand(
    name: 'history:build-statistics',
    description: 'Add a short description for your command',
)]
class HorizonctListTransactionsCommand extends Command
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        /* $customers = $this->doctrine->getRepository(HistoryTransactions::class, 'horizon'); */
        /* $history = $customers->findOneBy(['id' => 164908000931225600]); */

        $this->processLedgers();

        $io->success('Statistics builded for interval of 10minutes');

        return Command::SUCCESS;
    }

    private function processLedgers()
    {
        $endDate = $this->getLastLedgerTimestamp();
        $startDate = $this->getFirstLedgerTimestamp();
        $interval = new \DateInterval('PT10M');

        $currentBatchEnd = clone $endDate;
        while ($currentBatchEnd > $startDate) {
            $start = microtime(true);
            $batchStart = (clone $currentBatchEnd)->sub($interval);

            if ($batchStart < $startDate) {
                $batchStart = clone $startDate;
            }

            $this->dispatchProcessLedgers($batchStart, $currentBatchEnd);

            $time_elapsed_secs = microtime(true) - $start;
            dump('Time elapsed: ' . $time_elapsed_secs . ' - ' . $batchStart->format('Y-m-d H:i:s') . ' - ' . $currentBatchEnd->format('Y-m-d H:i:s'));

            $currentBatchEnd = $this->getPreviousBatchStart($batchStart); // Move to the previous batch

            if (!$currentBatchEnd) {
                break; // End processing if there are no more batches
            }
        }

        return Command::SUCCESS;
    }

    public function dispatchProcessLedgers($start, $end)
    {
        $ledgers = $this->getLedgers($start, $end);

        $summed = array_reduce($ledgers, function ($carry, $item) {
            foreach ($item as $key => $value) {
                if ($key === "closed_at" || $key === "sequence") {
                    continue;
                }
                if (isset($carry[$key])) {
                    $carry[$key] += $value;
                } else {
                    $carry[$key] = $value;
                }
            }
            return $carry;
        }, []);

        $ledgerIds = array_column($ledgers, 'sequence');
        $transactions = $this->getTransactions($ledgerIds);

        $totalLedgers = count($ledgers);
        $totalTransactions = count($transactions);

        $timeFrame = Timeframes::fromString('10m');

        $total_output = $this->getTotalOutput($transactions);
        $xmlTotalPayments = $this->getXmlPayments($transactions);
        $total_output = number_format($total_output, 8, '.', '');
        $xmlTotalPayments = number_format($xmlTotalPayments, 8, '.', '');
        /* $createAccountOp = $this->getCreateAccountOperations($transactions); */

        $timestamps = array_map(function ($entry) {
            return strtotime($entry['closed_at']);
        }, $ledgers);

        $differences = [];
        for ($i = 1; $i < count($timestamps); $i++) {
            $differences[] = $timestamps[$i] - $timestamps[$i - 1];
        }

        $average_time = array_sum($differences) / count($differences);

        $this->buildMetric(
            $timeFrame,
            'market-charts',
            'xml-payments',
            $xmlTotalPayments,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'network-charts',
            'total-output',
            $total_output,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'blockchain-charts',
            'total-ledgers',
            $totalLedgers,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'blockchain-charts',
            'number-of-transactions',
            $totalTransactions,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'blockchain-charts',
            'transactions-per-ledger',
            $totalLedgers > 0 ? $totalTransactions / $totalLedgers : 0,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'blockchain-charts',
            'number-of-operations',
            $summed['operation_count'],
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'blockchain-charts',
            'average-ledger-time',
            $average_time,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'network-charts',
            'successful-transactions',
            $summed['transaction_count'],
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'network-charts',
            'failed-transactions',
            $summed['failed_transaction_count'],
            $end
        );
        $this->buildMetric(
            $timeFrame,
            'network-charts',
            'transactions-per-second',
            $average_time > 0 ? $summed['transaction_count'] / $average_time : 0,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'network-charts',
            'operations-per-second',
            $average_time > 0 ? $summed['operation_count'] / $average_time : 0,
            $end
        );

        dump('Ledgers ' . $totalLedgers, 'Transaction ' . $totalTransactions);
    }

    public function buildMetric($timeframe, $chartType, $key, $value, $timestamp): void
    {
        $metric = new Metric();
        $metric->setChartType($chartType)
            ->setTimeframe($timeframe)
            ->setValue($value)
            ->setTimestamp($timestamp)
            ->setMetric($key);

        $this->entityManager->persist($metric);
        $this->entityManager->flush();
    }

    private function getCreateAccountOperations($transactions)
    {
        $ids = array_column($transactions, 'id');
        $ids = array_map('intval', $ids);

        $qb = $this->doctrine->getConnection('horizon')->createQueryBuilder();

        $qb->select("SUM(CAST(public.history_operations.details->>'starting_balance' AS numeric)) AS output_value")
            ->from('public.history_operations')
            ->where('public.history_operations.type = 0')
            ->andWhere("public.history_operations.details.details->>'asset_type' = 'native'")
            ->andWhere($qb->expr()->in('transaction_id', ':ids'))
            ->setParameter('ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        $result = $qb->executeQuery()->fetchAllAssociative();

        return $result;
    }

    private function getXmlPayments($transactions)
    {
        $ids = array_column($transactions, 'id');
        $ids = array_map('intval', $ids);

        $qb = $this->doctrine->getConnection('horizon')->createQueryBuilder();

        $qb->select("CAST(SUM(CAST(public.history_operations.details->>'amount' AS DOUBLE PRECISION)) AS NUMERIC(20,8)) AS output_value")
            ->from('public.history_operations')
            ->where($qb->expr()->in('type', [1, 2, 13]))
            ->andWhere("details->>'asset_type' = 'native'")
            ->andWhere($qb->expr()->in('transaction_id', ':ids'))
            ->setParameter('ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        $result = $qb->executeQuery()->fetchOne();

        return $result;
    }

    private function getTotalOutput($transactions)
    {
        $ids = array_column($transactions, 'id');
        $ids = array_map('intval', $ids);

        $qb = $this->doctrine->getConnection('horizon')->createQueryBuilder();

        $qb->select("CAST(SUM(CAST(public.history_operations.details->>'amount' AS DOUBLE PRECISION)) AS NUMERIC(20, 8)) AS output_value")
            ->from('public.history_operations')
            ->where($qb->expr()->in('type', [1, 2, 13]))
            ->andWhere($qb->expr()->in('transaction_id', ':ids'))
            ->setParameter('ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        $result = $qb->executeQuery()->fetchOne();

        return $result;
    }


    private function getLedgers($start, $end)
    {
        $sql = "
            SELECT sequence, closed_at, successful_transaction_count, tx_set_operation_count, operation_count, transaction_count,
            total_coins, failed_transaction_count
            FROM public.history_ledgers
            WHERE public.history_ledgers.closed_at >= :start_time
                AND public.history_ledgers.closed_at < :end_time
        ";
        $params = [
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time' => $end->format('Y-m-d H:i:s'),
        ];
        $ids = $this->doctrine->getConnection('horizon')->fetchAllAssociative($sql, $params);

        return $ids;
    }

    private function getTransactions($ledgerIds)
    {
        $qb = $this->doctrine->getConnection('horizon')->createQueryBuilder();

        $qb->select("id")
            ->from('public.history_transactions')
            ->andWhere($qb->expr()->in('ledger_sequence', ':ids'))
            ->setParameter('ids', $ledgerIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        $result = $qb->executeQuery()->fetchAllAssociative();
        return $result;
    }

    private function getLastLedgerTimestamp()
    {
        $sql = "SELECT MAX(closed_at) AS last_timestamp FROM public.history_ledgers";
        $conn = $this->doctrine->getConnection('horizon');
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return new \DateTime($result->fetchOne());
    }

    private function getPreviousBatchStart(\DateTime $before): ?\DateTime
    {
        $sql = "SELECT MAX(closed_at) AS prev_timestamp
            FROM public.history_ledgers
            WHERE closed_at < :before_time";

        $result = $this->doctrine->getConnection('horizon')
            ->fetchOne($sql, ['before_time' => $before->format('Y-m-d H:i:s')]);

        return $result ? new \DateTime($result) : null;
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


    public function importTransactions(string $cursor = 'now'): array
    {
        $connector = new HorizonConnector('history');
        $listTransactionsRequest = new ListTransactions($cursor);

        return $connector->send($listTransactionsRequest)->json();
    }
}
