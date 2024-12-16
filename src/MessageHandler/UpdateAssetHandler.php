<?php

namespace App\MessageHandler;

use App\Entity\StellarHorizon\Asset;
use App\Message\UpdateAsset;
use App\Repository\StellarHorizon\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
class UpdateAssetHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AssetRepository $assetRepository,
    ) {
    }

    public function __invoke(UpdateAsset $updateAsset): void
    {
        $assetDto = $updateAsset->getAssetResponse();

        $asset = $this->assetRepository->findOneBy([
            'asset_type' => $assetDto->getAssetType(),
            'asset_code' => $assetDto->getAssetCode(),
            'asset_issuer' => $assetDto->getAssetIssuer()
        ]);

        if (!$asset) {
            $asset = new Asset();
            $asset->setAssetType($assetDto->getAssetType())
                ->setAssetIssuer($assetDto->getAssetIssuer())
                ->setAssetCode($assetDto->getAssetCode());
        }

        $asset->setAccounts([
            'authorized' => $assetDto->getAccounts()->getAuthorized(),
            'authorized_to_maintain_liabilities' => $assetDto->getAccounts()->getAuthorizedToMaintainLiabilities(),
            'unauthorized' => $assetDto->getAccounts()->getUnauthorized()
        ])->setBalances([
            'authorized' => $assetDto->getBalances()->getAuthorized(),
            'authorized_to_maintain_liabilities' => $assetDto->getBalances()->getAuthorizedToMaintainLiabilities(),
            'unauthorized' => $assetDto->getBalances()->getUnauthorized()
        ])->setFlags([
            'auth_required' => $assetDto->getFlags()->isAuthRequired(),
            'auth_revocable' => $assetDto->getFlags()->isAuthRevocable(),
            'auth_immutable' => $assetDto->getFlags()->isAuthImmutable(),
            'auth_clawback_enabled' => $assetDto->getFlags()->isAuthClawbackEnabled(),
        ])
          ->setAmount($assetDto->getBalances()->getAuthorized())
          ->setNumAccounts($assetDto->getAccounts()->getAuthorized())
          ->setLiquidityPoolsAmount($assetDto->getLiquidityPoolsAmount())
          ->setNumLiquidityPools($assetDto->getNumLiquidityPools())
          ->setClaimableBalancesAmount($assetDto->getClaimableBalancesAmount())
          ->setNumClaimableBalances($assetDto->getNumClaimableBalances())
          ->setNumContracts($assetDto->getNumContracts())
          ->setContractsAmount($assetDto->getContractsAmount())
          ->setNumArchivedContracts($assetDto->getNumArchivedContracts())
          ->setUpdatedAt(new \DateTimeImmutable())
          ->setArchivedContractsAmount($assetDto->getArchivedContractsAmount())
          ->setToml($assetDto->getLinks()->getToml()->getHref());

        $this->entityManager->persist($asset);
        $this->entityManager->flush();
    }
}
