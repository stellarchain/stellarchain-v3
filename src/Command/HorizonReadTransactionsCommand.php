<?php

namespace App\Command;

use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\ListTransactions;
use App\Message\ProcessTransaction;
use App\Utils\Helper;
use Soneso\StellarSDK\Responses\Transaction\TransactionResponse;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'horizon:read-transactions',
    description: 'Listen transactions on Stellar blockchain.',
)]
class HorizonReadTransactionsCommand extends Command
{
    public function __construct(
        private MessageBusInterface $bus,
        private Helper $helper
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Started transactions listener.');

        $cursor = 'now';
        while (true) {
            $transactionsResponse = $this->importTransactions($cursor);
            $transactions = $transactionsResponse['_embedded']['records'];
            foreach ($transactions as $transaction) {
                $this->dispatchTransaction($transaction);
            }

            $cursor = $this->helper->getUrlParams($transactionsResponse['_links']['next']['href'])['cursor'];
        }
        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    public function importTransactions(string $cursor): array
    {
        $connector = new HorizonConnector('public');
        $listTransactionsRequest = new ListTransactions($cursor);

        return $connector->send($listTransactionsRequest)->json();
    }

    /**
     * @param array<int,mixed> $transaction
     */
    public function dispatchTransaction(array $transaction): void
    {
        $transactionResponse = TransactionResponse::fromJson($transaction);
        if ($transactionResponse instanceof TransactionResponse) {
            $this->bus->dispatch(new ProcessTransaction($transactionResponse));
        }
    }
}
