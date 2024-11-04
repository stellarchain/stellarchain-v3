<?php

namespace App\Command;

use App\Service\StellarBigQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'statistics:build',
    description: 'Add a short description for your command',
)]
class StatisticsBuildCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $stellarQuery = new StellarBigQuery();
        $timeframes = ['5m', '1h', '1d', '7d'];

        $pageData = $this->coinStatRepository->getStatsByName($key, 0, 30);
        $priceData = array_reverse($pageData);

        $endDate = new \DateTimeImmutable();
        $startDate = $endDate->sub(new \DateInterval('PT5H'));
        $ledgerMetrics = $this->ledgerMetricsService->getMetricsForTimeIntervals($startDate, $endDate, 1, 50);

        $top100ActiveAddresses = DB::connection('pgsql')
                ->query()
                ->fromSub(function ($query) {
                    $query->from('public.accounts')
                        ->where('balance', '!=', 0)
                        ->orderBy('balance', 'desc')
                        ->limit(100)
                        ->select('balance');
                }, 'sub')
            ->avg('balance');

        $activeAddresses = DB::connection('pgsql')
            ->table('public.accounts')
            ->where('balance', '!=', 0)
            ->count();

        $inactiveAddresses = DB::connection('pgsql')
            ->table('public.accounts')
            ->where('balance', '=', 0)
            ->count();

        $averageLedgerSize = DB::connection('pgsql')
            ->query()
            ->fromSub(function ($query) {
                $query->from('public.history_ledgers')
                    ->join('public.history_transactions', 'public.history_ledgers.sequence', '=', 'public.history_transactions.ledger_sequence')
                    ->join('public.history_operations', 'public.history_operations.transaction_id', '=', 'public.history_transactions.id')
                    ->groupBy('public.history_ledgers.sequence')
                    ->whereRaw("public.history_ledgers.closed_at > TIMEZONE('utc', NOW()) - INTERVAL '5 minutes'")
                    ->selectRaw('sum(pg_column_size(public.history_ledgers.*)) + sum(pg_column_size(public.history_transactions.*)) + sum(pg_column_size(public.history_operations.*)) ledger_size');
            }, 'sub')
            ->selectRaw('AVG(ledger_size) avg_ledger_size')
            ->first();

        $top100ActiveAddressesAvgBalance = Account::where('balance', '!=', 0)
            ->orderByDesc('balance')
            ->limit(100)
            ->avg('balance');

        dd($top100ActiveAddresses);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
