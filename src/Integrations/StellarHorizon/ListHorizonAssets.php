<?php

namespace App\Integrations\StellarHorizon;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListHorizonAssets extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $cursor) {
    }

    public function resolveEndpoint(): string
    {
        return '/assets';
    }

    protected function defaultQuery(): array
    {
        return [
            'limit' => 200,
            'order' => 'asc',
            'cursor' => $this->cursor
        ];
    }
}
