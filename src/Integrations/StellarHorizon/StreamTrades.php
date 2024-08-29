<?php

namespace App\Integrations\StellarHorizon;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class StreamTrades extends Request
{
    protected Method $method = Method::GET;
    public function __construct(protected readonly string $cursor) {
    }


    public function resolveEndpoint(): string
    {
        return '/trades';
    }

    public function defaultConfig(): array
    {
        return [
            'stream' => true,
            'read_timeout' => null
        ];
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'text/event-stream',
        ];
    }

    protected function defaultQuery(): array
    {
        return [
            'cursor' => $this->cursor,
            'limit' => 200
        ];
    }
}
