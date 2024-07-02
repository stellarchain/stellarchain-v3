<?php

namespace App\Integrations\CoinMarketCap;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetStellarPerformanceStatsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/cryptocurrency/price-performance-stats/latest?symbol=XLM&time_period=all_time,24h,7d';
    }
}
