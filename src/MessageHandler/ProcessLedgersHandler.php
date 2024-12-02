<?php

namespace App\MessageHandler;

use App\Config\Timeframes;
use App\Entity\Metric;
use App\Message\ProcessInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessLedgersHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ProcessInterval $interval): void
    {
        $this->dispatchProcessLedgers($interval->getStart(), $interval->getEnd());
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

        $max_value = 999999999999;  // 10^12 - 1
        $total_output = min($total_output, $max_value);
        $xmlTotalPayments = min($xmlTotalPayments, $max_value);
        /* $createAccountOp = $this->getCreateAccountOperations($transactions); */

        $timestamps = array_map(function ($entry) {
            return strtotime($entry['closed_at']);
        }, $ledgers);

        $differences = [];
        for ($i = 1; $i < count($timestamps); $i++) {
            $differences[] = $timestamps[$i] - $timestamps[$i - 1];
        }

        $average_time = number_format(array_sum($differences) / count($differences), 2 , '.', '');

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
            $totalLedgers > 0 ? (int)($totalTransactions / $totalLedgers) : 0,
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
            $average_time > 0 ? (int)($summed['transaction_count'] / $average_time) : 0,
            $end
        );

        $this->buildMetric(
            $timeFrame,
            'network-charts',
            'operations-per-second',
            $average_time > 0 ? (int)($summed['operation_count'] / $average_time) : 0,
            $end
        );

        dump('Ledgers ' . $totalLedgers, 'Transaction ' . $totalTransactions);
    }

    public function buildMetric($timeframe, $chartType, $key, $value, $timestamp): void
    {
        dump($key.' - '.$value);
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


}
