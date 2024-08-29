<?php
namespace App\Message;

use Soneso\StellarSDK\Responses\Asset\AssetResponse;

class UpdateAsset
{
    public function __construct(
        private AssetResponse $asset,
    ) {
    }

    public function getAssetResponse(): AssetResponse
    {
        return $this->asset;
    }
}
