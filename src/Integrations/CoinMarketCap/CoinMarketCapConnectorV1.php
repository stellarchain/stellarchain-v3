<?php

namespace App\Integrations\CoinMarketCap;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\HasTimeout;

class CoinMarketCapConnectorV1 extends Connector
{
    use HasTimeout;

    protected int $connectTimeout = 60;

    protected int $requestTimeout = 120;

    public function resolveBaseUrl(): string
    {
        return 'https://pro-api.coinmarketcap.com/v1';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-CMC_PRO_API_KEY' => '67437685-fee7-4e0e-85c1-e5266bc67090'
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'stream' => true,
        ];
    }
}
