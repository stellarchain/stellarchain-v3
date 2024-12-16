<?php

namespace App\Command\Statistics;

use App\Config\Timeframes;
use App\Repository\CoinStatRepository;
use App\Service\StatisticsService;
use App\Service\LedgerMetricsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ProcessInterval;

#[AsCommand(
    name: 'statistics:build',
    description: 'Build blockchain statistics.',
)]
class StatisticsBuildCommand extends Command
{
    public function __construct(
        private CoinStatRepository $coinStatRepository,
        private StatisticsService $statisticsService,
        private LedgerMetricsService $ledgerMetricsService,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
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

        $batchEndDate = new \DateTime(); // Create the end date
        $batchStartDate = (clone $batchEndDate)->sub(new \DateInterval($interval)); // Clone and subtract the interval

        $this->bus->dispatch(new ProcessInterval($batchStartDate, $batchEndDate));

        $io->success('Statistics builded.');

        return Command::SUCCESS;
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
