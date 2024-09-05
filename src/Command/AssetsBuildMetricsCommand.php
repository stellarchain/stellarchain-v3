<?php

namespace App\Command;

use App\Entity\StellarHorizon\Asset;
use App\Entity\StellarHorizon\AssetMetric;
use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\SingleAsset;
use App\Message\UpdateAsset;
use App\Repository\StellarHorizon\AssetMetricRepository;
use App\Repository\StellarHorizon\AssetRepository;
use App\Repository\StellarHorizon\TradeRepository;
use App\Service\GlobalValueService;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Responses\Asset\AssetResponse;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'market:build-asset-metrics',
    description: 'Build asset metrics.(we run this command hourly as cron)',
)]
class AssetsBuildMetricsCommand extends Command
{
    public function __construct(
        private AssetMetricRepository $assetMetricRepository,
        private AssetRepository $assetRepository,
        private TradeRepository $tradeRepository,
        private EntityManagerInterface $entityManager,
        private GlobalValueService $globalValueService,
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $offset = 0;
        $batchSize = 20;
        $nativeAsset = $this->assetRepository->findOneBy(['asset_type' => 'native']);
        do {
            $assets = $this->assetRepository->findBy([], null, $batchSize, $offset);
            foreach ($assets as $asset) {
                $this->processAsset($asset, $nativeAsset);
            }
            $offset += $batchSize;
            $this->entityManager->clear();
        } while (count($assets) > 0);

        $io->success('Build assets metrics successfully.');
        return Command::SUCCESS;
    }

    private function processAsset(Asset $asset, Asset $nativeAsset): void
    {
        $currentDateTime = new \DateTime();
        $interval = new \DateInterval('PT3H');
        $cutoffTime = (clone $currentDateTime)->sub($interval);
        $latestPriceResult = $this->tradeRepository->findOneBy(['base_asset' => $nativeAsset, 'counter_asset' => $asset], ['ledger_close_time' => 'DESC']);
        $roundedDateTime = $currentDateTime->setTime($currentDateTime->format('H'), 0, 0);
        $roundedDateTime7dAgo = (clone $roundedDateTime)->modify('-7 days');
        $roundedDateTime24hAgo = (clone $roundedDateTime)->modify('-24 hours');
        $usdXlmPrice = $this->globalValueService->getPrice();

        if ($latestPriceResult && !empty($asset->getAssetCode())) {
            $latestPrice = (float)$latestPriceResult->getPrice();
            $price1hAgo = $this->tradeRepository->getPriceAt($asset, $nativeAsset, $roundedDateTime);

            if ($price1hAgo) {
                $price24hAgo = $this->tradeRepository->getPriceAt($asset, $nativeAsset, $roundedDateTime24hAgo);
                $price7dAgo = $this->tradeRepository->getPriceAt($asset, $nativeAsset, $roundedDateTime7dAgo);

                $priceChange1h = $this->calculatePriceChange($latestPrice, $price1hAgo);
                $priceChange24h = $this->calculatePriceChange($latestPrice, $price24hAgo);
                $priceChange7d = $this->calculatePriceChange($latestPrice, $price7dAgo);

                $volume24h = $this->tradeRepository->findSumByAssets($asset, $nativeAsset, $roundedDateTime24hAgo);
                $volume1h = $this->tradeRepository->findSumByAssets($asset, $nativeAsset, $roundedDateTime);
                $totalTrades = $this->tradeRepository->countTotalTrades($asset, $nativeAsset, $roundedDateTime);

                if ($volume24h['baseAmount']) {
                    $priceInUsd = (1 / $latestPrice * $usdXlmPrice);
                    $assetMetric = new AssetMetric();
                    $assetMetric->setAsset($asset)
                        ->setPrice(1 / $latestPrice)
                        ->setVolume24h($volume24h['baseAmount'])
                        ->setCirculatingSupply(0)
                        ->setPriceChange1h($priceChange1h)
                        ->setPriceChange24h($priceChange24h)
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setPriceChange7d($priceChange7d);

                    $isInMarket = ($priceInUsd * $volume1h['baseAmount']) > 10;

                    if ($isInMarket && false) {
                        $asset->setInMarket($isInMarket);
                        $this->entityManager->persist($asset);
                    }

                    if ($asset->getUpdatedAt() <= $cutoffTime && $asset->isInMarket()) {
                        dump(
                            'Asset: ' . $asset->getAssetCode(),
                            'Price: ' . $latestPrice,
                            'XLM Price: ' . 1 / $latestPrice,
                            'USD Price: ' . $priceInUsd,
                            'Price 1h ago: ' . 1 / $price1hAgo,
                            'Price 24h ago: ' . 1 / $price24hAgo,
                            'Price 7d ago: ' . 1 / $price7dAgo,
                            'Price % 1h ago: ' . $priceChange1h,
                            'Price % 24h ago: ' . $priceChange24h,
                            'Price % 7d ago: ' . $priceChange7d,
                            'Total trades in 1h' . $totalTrades,
                            'Volume 24H: ' . $volume24h['baseAmount'],
                            'Volume 1h: ' . $volume1h['baseAmount'],
                            '======================================='
                        );
                        $assetData = $this->importAsset($asset->getAssetCode(), $asset->getAssetIssuer());
                        $assetResponse = AssetResponse::fromJson($assetData['_embedded']['records'][0]);
                        if ($assetResponse instanceof AssetResponse) {
                            $this->bus->dispatch(new UpdateAsset($assetResponse));
                        }
                    }
                    $this->entityManager->persist($assetMetric);
                    $this->entityManager->flush();
                }
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
}
