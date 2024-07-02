<?php
namespace App\Integrations\CoinMarketCap;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetStellarRealTimeDataRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/cryptocurrency/listings/latest?cryptocurrency_type=coins&convert=usd';
    }
}
