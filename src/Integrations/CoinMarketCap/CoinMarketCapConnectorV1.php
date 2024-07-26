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
            'X-CMC_PRO_API_KEY' => '173fbe8d-15b6-4d7f-a8c0-505e026fb603'
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'stream' => true,
        ];
    }
}
