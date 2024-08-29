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
            'cookie' => 'ignite=5062e123aa31806c4f7738d1336f488d',
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
