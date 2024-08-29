<?php

namespace App\Command;

use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\ListHorizonTrades;
use App\Message\StoringTrade;
use App\Utils\Helper;
use Soneso\StellarSDK\Responses\Trades\TradeResponse;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'horizon:read-trades',
    description: 'Listen Liquidity Pools and OrderBook trades on Stellar blockchain.',
)]
class HorizonReadTradesCommand extends Command
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
        $io->success('Start reading treads.');

        $cursor = 'now';
        while (true) {
            $tradesResponse = $this->importTrades($cursor);
            $trades = $tradesResponse['_embedded']['records'];
            foreach ($trades as $trade) {
                $this->dispatchTrade($trade);
            }
            $cursor = $this->helper->getUrlParams($tradesResponse['_links']['next']['href'])['cursor'];
        }

        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    public function importTrades(string $cursor): array
    {
        $connector = new HorizonConnector('public');
        $listTradesRequest = new ListHorizonTrades($cursor);

        return $connector->send($listTradesRequest)->json();
    }

    /**
     * @param array<int,mixed> $transaction
     */
    public function dispatchTrade(array $trade): void
    {
        $tradeResponse = TradeResponse::fromJson($trade);
        if ($tradeResponse instanceof TradeResponse) {
            $this->bus->dispatch(new StoringTrade($tradeResponse));
        }
    }
}
