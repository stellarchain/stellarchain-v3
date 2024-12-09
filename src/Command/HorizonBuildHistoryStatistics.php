<?php

namespace App\Command;

use App\Message\ProcessInterval;
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
class HorizonBuildHistoryStatistics extends Command
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
            ->addArgument('start_date', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $startDate = $input->getArgument('start_date');
        if ($startDate) {
            $io->note(sprintf('You passed an argument: %s', $startDate));
        }

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

            dump('Time elapsed: ' . microtime(true) - $start . ' - ' . $batchStart->format('Y-m-d H:i:s') . ' - ' . $currentBatchEnd->format('Y-m-d H:i:s'));

            $currentBatchEnd = $this->getPreviousBatchStart($batchStart);

            if (!$currentBatchEnd) {
                break;
            }
        }

        $io->success('Statistics builded for interval of 10minutes');

        return Command::SUCCESS;
    }

    private function getLastLedgerTimestamp(): \DateTime
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

    private function getFirstLedgerTimestamp(): \DateTime
    {
        $sql = "SELECT MIN(closed_at) AS first_timestamp FROM public.history_ledgers";
        $conn = $this->doctrine->getConnection('horizon');
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return new \DateTime($result->fetchOne());
    }
}
