<?php

namespace App\Integrations\StellarHorizon;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListTransactions extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $cursor) {
    }

    public function resolveEndpoint(): string
    {
        return '/transactions';
    }

    protected function defaultQuery(): array
    {
        return [
        ];
    }
}
