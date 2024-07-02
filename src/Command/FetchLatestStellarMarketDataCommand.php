<?php

namespace App\Command;

use App\Entity\Coin;
use App\Entity\CoinStat;
use App\Integrations\CoinMarketCap\CoinMarketCapConnectorV1;
use App\Integrations\CoinMarketCap\GetStellarRealTimeDataRequest;
use App\Repository\CoinStatRepository;
use App\Service\NumberFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsCommand(
    name: 'market:fetch-stellar-real-time-data',
    description: 'We fetch real-time data about Stellar cryptocurrency.',
)]
class FetchLatestStellarMarketDataCommand extends Command
{

    public function __construct(
        public EntityManagerInterface $entityManager,
        public HubInterface $hub,
        private NumberFormatter $numberFormatter,
        public CoinStatRepository $coinStatRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $coinMarketCap = new CoinMarketCapConnectorV1();
        $request = new GetStellarRealTimeDataRequest();
        $responseData = $coinMarketCap->send($request)->json();
        $coins = new ArrayCollection(isset($responseData['data']) ? $responseData['data'] : []);

        $stellar = $this->entityManager->getRepository(Coin::class)->findOneBySymbol('XLM');
        $stellarData = $coins->findFirst(function ($key, $coin) {
            return $coin['symbol'] == 'XLM';
        });

        if ($stellarData) {
            $coinStats = [
                'price_usd' => round($stellarData['quote']['USD']['price'], 5),
                'volume_24h' => round($stellarData['quote']['USD']['volume_24h'], 4),
                'market_cap_dominance' => round($stellarData['quote']['USD']['market_cap_dominance'], 4),
                'market_cap'  => round($stellarData['quote']['USD']['market_cap'], 4),
                'circulating_supply' => round($stellarData['circulating_supply'], 4),
                'rank' => $stellarData['cmc_rank'],
            ];

            foreach ($coinStats as $name => $value) {
                $stellarCoinStat = new CoinStat();
                $stellarCoinStat->setName($name);
                $stellarCoinStat->setValue($value);
                $stellarCoinStat->updateTimestamps();
                $stellar->addCoinStat($stellarCoinStat);
                $this->entityManager->persist($stellarCoinStat);
            }

            $this->entityManager->persist($stellar);
            $this->entityManager->flush();

            $io->success('Stellar XLM real-time data fetched successfully.');

            $requiredStats = ['rank', 'market_cap', 'volume_24h', 'price_usd', 'circulating_supply', 'market_cap_dominance'];
            $stellarCoinStats = $this->coinStatRepository->findLatestAndPreviousBySymbol('XLM', $requiredStats);
            $formattedStats = [];
            foreach ($stellarCoinStats as $stat) {
                $stat['value'] = $this->numberFormatter->formatLargeNumber($stat['value']);
                $stat['prev_value'] = $this->numberFormatter->formatLargeNumber($stat['prev_value']);
                $formattedStats[$stat['name']] = $stat;
            }

            $this->hub->publish(new Update(
                'stellar-real-time-data',
                json_encode($formattedStats)
            ));

            return Command::SUCCESS;
        }

        $io->error('Something its wrong! Check your command.');

        return Command::FAILURE;
    }
}
