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

#[AsCommand(
    name: 'history:list-transactions',
    description: 'Add a short description for your command',
)]
class HorizonctListTransactionsCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        $transactions = $this->importTransactions();

        dump($transactions);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    public function importTransactions(string $cursor = 'now'): array
    {
        $connector = new HorizonConnector('history');
        $listTransactionsRequest = new ListTransactions($cursor);

        return $connector->send($listTransactionsRequest)->json();
    }
}
