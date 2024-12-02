<?php

namespace App\Command;

use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\ListTransactions;
use App\Message\ProcessInterval;
use App\Message\StoringTrade;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'history:build-statistics',
    description: 'Add a short description for your command',
)]
class HorizonctListTransactionsCommand extends Command
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('date', InputArgument::OPTIONAL, 'Argument description');
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

            $this->bus->dispatch(new ProcessInterval($batchStart, $currentBatchEnd));

            $time_elapsed_secs = microtime(true) - $start;
            dump('Time elapsed: ' . $time_elapsed_secs . ' - ' . $batchStart->format('Y-m-d H:i:s') . ' - ' . $currentBatchEnd->format('Y-m-d H:i:s'));

            $currentBatchEnd = $this->getPreviousBatchStart($batchStart); // Move to the previous batch

            if (!$currentBatchEnd) {
                break; // End processing if there are no more batches
            }
        }

        return Command::SUCCESS;

        $io->success('Statistics builded for interval of 10minutes');

        return Command::SUCCESS;
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
