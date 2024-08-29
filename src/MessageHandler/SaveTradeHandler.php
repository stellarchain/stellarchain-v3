<?php

namespace App\MessageHandler;

use App\Entity\StellarHorizon\Asset;
use App\Entity\StellarHorizon\Trade;
use App\Message\StoringTrade;
use App\Repository\StellarHorizon\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SaveTradeHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AssetRepository $assetRepository,
    ) {
    }

    public function __invoke(StoringTrade $tradeResponse): void
    {
        $response = $tradeResponse->getTradeResponse();

        $baseAsset = $this->assetRepository->findOneBy([
            'asset_type' => $response->getBaseAssetType(),
            'asset_code' => $response->getBaseAssetCode(),
            'asset_issuer' => $response->getBaseAssetIssuer()
        ]);

        if (!$baseAsset) {
            $baseAsset = new Asset();
            $baseAsset->setAssetType($response->getBaseAssetType())
                ->setAssetIssuer($response->getBaseAssetIssuer())
                ->setAssetCode($response->getBaseAssetCode());

            $this->entityManager->persist($baseAsset);
            $this->entityManager->flush();
        }

        $counterAsset = $this->assetRepository->findOneBy([
            'asset_type' => $response->getCounterAssetType(),
            'asset_code' => $response->getCounterAssetCode(),
            'asset_issuer' => $response->getCounterAssetIssuer()
        ]);

        if (!$counterAsset) {
            $counterAsset = new Asset();
            $counterAsset->setAssetType($response->getCounterAssetType())
                ->setAssetIssuer($response->getCounterAssetIssuer())
                ->setAssetCode($response->getCounterAssetCode());

            $this->entityManager->persist($counterAsset);
            $this->entityManager->flush();
        }

        $price = $response->getPrice()->getN() / $response->getPrice()->getD();
        $trade = new Trade();

        // Standard mapping
        $trade->setBaseAsset($baseAsset)
            ->setCounterAsset($counterAsset);

        $trade->setBaseAmount($response->getBaseAmount())
            ->setCounterAmount($response->getCounterAmount());

        $trade->setBaseOfferId($response->getBaseOfferId())
            ->setCounterOfferId($response->getCounterOfferId())
            ->setBaseAccount($response->getBaseAccount())
            ->setCounterAccount($response->getCounterAccount());

        $trade->setBaseLiquidityPoolId($response->getBaseLiquidityPoolId())
            ->setCounterLiquidityPoolId($response->getCounterLiquidityPoolId());

        $trade->setPagingToken($response->getPagingToken())
            ->setLedgerCloseTime(\DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $response->getLedgerCloseTime()))
            ->setTradeType($response->getTradeType())
            ->setPrice($price)
            ->setBaseIsSeller($response->isBaseIsSeller());

        $this->entityManager->persist($trade);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
