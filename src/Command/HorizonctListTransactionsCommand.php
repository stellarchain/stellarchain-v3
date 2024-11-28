<?php

namespace App\Command;

use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\ListTransactions;
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

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    public function importTransactions(string $cursor = 'now'): array
    {
        $connector = new HorizonConnector('history');
        $listTransactionsRequest = new ListTransactions($cursor);

        return $connector->send($listTransactionsRequest)->json();
    }

    private function processLedgers()
    {
        $startDate = $this->getFirstLedgerTimestamp();
        $endDate = new \DateTime();
        $interval = new \DateInterval('PT10M');

        $currentBatchStart = clone $startDate;
        while ($currentBatchStart < $endDate) {
            $batchEnd = (clone $currentBatchStart)->add($interval);

            $ledgers = $this->getLedgers($currentBatchStart, $batchEnd);
            $transactions = $this->getTransactions($ledgers);

            $total_output = $this->getTotalOutput($transactions);

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

    private function getTotalOutput($transactions)
    {
        $ids = array_column($transactions, 'id');
        $ids = array_map('intval', $ids);

        $qb = $this->doctrine->getConnection('horizon')->createQueryBuilder();

        $qb->select("SUM(CAST(public.history_operations.details->>'amount' AS DOUBLE PRECISION)) AS output_value")
            ->from('public.history_operations')
            ->where($qb->expr()->in('type', [1, 2, 13]))
            ->andWhere($qb->expr()->in('transaction_id', ':ids'))
            ->setParameter('ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        $result = $qb->executeQuery()->fetchAllAssociative();

        return $result;
    }

    private function getLedgers($start, $end)
    {
        $sql = "
            SELECT sequence
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
        $sql = "
            SELECT id FROM public.history_transactions
            WHERE public.history_transactions.ledger_sequence
            IN ( :ledgerIds )
        ";
        $ids = $this->doctrine->getConnection('horizon')->fetchAllAssociative($sql, [
            'ledgerIds' => $ledgerIds
        ]);

        return $ids;
    }
}
