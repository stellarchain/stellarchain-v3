<?php

namespace App\Integrations\StellarHorizon;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SingleAsset extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $assetCode, protected string $assetIssuer) {
    }

    public function resolveEndpoint(): string
    {
        return '/assets';
    }

    protected function defaultQuery(): array
    {
        return [
            'asset_code' => $this->assetCode,
            'asset_issuer' => $this->assetIssuer
        ];
    }
}
