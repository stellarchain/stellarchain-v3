<?php

namespace App\Integrations\StellarCommunityFund;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\HasTimeout;


class SCFConnector extends Connector
{
    use HasTimeout;

    protected int $connectTimeout = 60;

    protected int $requestTimeout = 120;

    public function resolveBaseUrl(): string
    {
        return 'https://dashboard.communityfund.stellar.org/api/v4';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'cookie' => 'ignite=542b9222b1d91a3a5e4c076d850c9d4a; PHPSESSID=3e3d3edb3624f8c82fce1dafe4559899',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36'
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'stream' => true,
        ];
    }
}
