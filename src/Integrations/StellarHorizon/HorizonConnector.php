<?php

namespace App\Integrations\StellarHorizon;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\HasTimeout;


class HorizonConnector extends Connector
{
    use HasTimeout;

    protected int $connectTimeout = 0;

    protected int $requestTimeout = 0;

    public function __construct(protected string $instance = 'public') {
    }

    public function resolveBaseUrl(): string
    {
        switch($this->instance) {
            case 'testnet':
                $baseUrl = 'https://horizon-testnet.stellar.org/';
                break;
            case 'public':
            default:
                $baseUrl = 'https://horizon.stellar.org';
                break;
        }
        return $baseUrl;
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
