<?php

namespace App\Command\Market;

use App\Entity\Horizon\HistoryAssets;
use App\Entity\Horizon\HistoryTrades;
use App\Entity\StellarHorizon\Asset;
use App\Entity\StellarHorizon\AssetMetric;
use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\SingleAsset;
use App\Message\UpdateAsset;
use App\Repository\StellarHorizon\AssetMetricRepository;
use App\Repository\StellarHorizon\AssetRepository;
use App\Service\GlobalValueService;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Responses\Asset\AssetResponse;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\Persistence\ManagerRegistry;

#[AsCommand(
    name: 'market:build-statistics',
    description: 'Build asset metrics.(we run this command hourly as cron)',
)]
class BuildMarketStatistics extends Command
{
    public function __construct(
        private AssetMetricRepository $assetMetricRepository,
        private AssetRepository $assetRepository,
        private EntityManagerInterface $entityManager,
        private GlobalValueService $globalValueService,
        private MessageBusInterface $bus,
        private ManagerRegistry $doctrine,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $offset = 0;
        $batchSize = 20;

        do {
            $assets = $this->assetRepository->findBy(['in_market' => true], null, $batchSize, $offset);
            foreach ($assets as $asset) {
                $this->processAsset($asset);
            }
            $offset += $batchSize;
            $this->entityManager->clear();
        } while (count($assets) > 0);

        $io->success('Build assets metrics successfully.');

        return Command::SUCCESS;
    }

    private function processAsset(Asset $asset): void
    {
        $currentDateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $interval = new \DateInterval('PT12H');
        $cutoffTime = (clone $currentDateTime)->sub($interval);

        $usdXlmPrice = $this->globalValueService->getPrice();

        $horizonTradeRepository = $this->doctrine->getRepository(HistoryTrades::class, 'horizon');
        $horizonAssetRepository = $this->doctrine->getRepository(HistoryAssets::class, 'horizon');

        $baseAssetNative = $horizonAssetRepository->findOneBy(['asset_type' => 'native']);
        $counterAsset = $horizonAssetRepository->findOneBy(['asset_code' => $asset->getAssetCode(), 'asset_issuer' => $asset->getAssetIssuer()]);


        if ($baseAssetNative->getId() < $counterAsset->getId()) {
            $baseAsset = $baseAssetNative;
            $reversed = true;
        } else {
            $baseAsset = $counterAsset;
            $counterAsset = $baseAssetNative;
            $reversed = false;
        }

        $latestPriceResult = $horizonTradeRepository->findOneBy(
            [
                'counter_asset_id' => $counterAsset->getId(),
                'base_asset_id' => $baseAsset->getId(),
            ],
            ['ledger_closed_at' => 'DESC']
        );

        $roundedDateTime = (clone $currentDateTime)->modify('-1 hour');
        $roundedDateTime7dAgo = (clone $currentDateTime)->modify('-7 days');
        $roundedDateTime24hAgo = (clone $currentDateTime)->modify('-24 hours');

        if ($latestPriceResult) {
            $latestPrice = ($reversed) ? $latestPriceResult->getPriceD() / $latestPriceResult->getPriceN() : $latestPriceResult->getPriceN() / $latestPriceResult->getPriceD();
            $price1hAgo = $horizonTradeRepository->getPriceAt($baseAsset, $counterAsset, $roundedDateTime, $reversed);

            if ($price1hAgo) {

                $price24hAgo = $horizonTradeRepository->getPriceAt($baseAsset, $counterAsset, $roundedDateTime24hAgo, $reversed);
                $price7dAgo = $horizonTradeRepository->getPriceAt($baseAsset, $counterAsset, $roundedDateTime7dAgo, $reversed);

                $priceChange1h = $this->calculatePriceChange($latestPrice, $price1hAgo);
                $priceChange24h = $this->calculatePriceChange($latestPrice, $price24hAgo);
                $priceChange7d = $this->calculatePriceChange($latestPrice, $price7dAgo);

                $volume24h = $horizonTradeRepository->findSumByAssets($baseAsset, $counterAsset, $roundedDateTime24hAgo);
                $volume1h = $horizonTradeRepository->findSumByAssets($baseAsset, $counterAsset, $roundedDateTime);
                $totalTrades = $horizonTradeRepository->countTotalTrades($baseAsset, $counterAsset, $roundedDateTime24hAgo);

                if ($volume24h['baseAmount']) {
                    $priceInUsd = ($latestPrice) * $usdXlmPrice;
                    $assetMetric = new AssetMetric();
                    $assetMetric->setAsset($asset)
                        ->setPrice($latestPrice)
                        ->setVolume24h($volume24h['baseAmount'])
                        ->setCirculatingSupply(0)
                        ->setPriceChange1h($priceChange1h)
                        ->setPriceChange24h($priceChange24h)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setTotalTrades($totalTrades)
                        ->setPriceChange7d($priceChange7d);

                    $rankScore = $this->calculateAssetRank(
                        $priceInUsd,
                        $volume24h['baseAmount'] * $priceInUsd,
                        $totalTrades,
                        $priceChange1h,
                        $priceChange24h,
                        $priceChange7d,
                    );

                    dump(
                        'Asset: ' . $asset->getAssetCode(),
                        'XLM Price: ' . $latestPrice,
                        'USD Price: ' . $priceInUsd,
                        'Price 1h ago: ' . $price1hAgo,
                        'Price 24h ago: ' . $price24hAgo,
                        'Price 7d ago: ' . $price7dAgo,
                        'Price % 1h ago: ' . $priceChange1h,
                        'Price % 24h ago: ' . $priceChange24h,
                        'Price % 7d ago: ' . $priceChange7d,
                        'Total trades in 24h: ' . $totalTrades,
                        'Volume 24H in USD: ' . $volume24h['baseAmount'] * $priceInUsd,
                        'Volume 1h: ' . $volume1h['baseAmount'],
                        'Rank Score: ' . $rankScore,
                        '======================================='
                    );

                    $asset->setRankRaw($rankScore);
                    $this->entityManager->persist($assetMetric);
                    $this->entityManager->flush();
                }
            }
        }

        if ($asset->getUpdatedAt() <= $cutoffTime && $asset->isInMarket()) {
            $assetData = $this->importAsset($asset->getAssetCode(), $asset->getAssetIssuer());
            $assetResponse = AssetResponse::fromJson($assetData['_embedded']['records'][0]);
            if ($assetResponse instanceof AssetResponse) {
                $this->bus->dispatch(new UpdateAsset($assetResponse));
            }
        }
    }

    private function calculatePriceChange(float $latestPrice, ?float $previousPrice): ?float
    {
        if ($previousPrice === null || $previousPrice == 0) {
            return null;
        }
        $change = (($latestPrice - $previousPrice) / $previousPrice) * 100;
        return round($change, 2);
    }

    public function importAsset(string $assetCode, string $assetIssuer): array
    {
        $connector = new HorizonConnector();
        $listAssetRequest = new SingleAsset($assetCode, $assetIssuer);

        return $connector->send($listAssetRequest)->json();
    }

    function calculateAssetRank($latestPrice, $volume24hInUsd, $totalTrades)
    {
        $wPrice = 0.01;  // 10% weight for price
        $wVolume = 0.1; // 20% weight for volume
        $wTrades = 0.8; // 10% weight for total trades

        // Raw values (Price is inverted to match the previous logic)
        $priceScore = 1 / $latestPrice;
        $totalTradesScore = $totalTrades;

        // Final rank score calculation using the weighted sum of the raw values
        $rankScore = ($wPrice * $priceScore) +
            ($wVolume * $volume24hInUsd) +
            ($wTrades * $totalTradesScore);

        return number_format($rankScore, 5, '.', '');
    }
}
