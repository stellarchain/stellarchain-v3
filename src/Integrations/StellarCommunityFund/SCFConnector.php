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
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'stream' => true,
        ];
    }
}
