<?php

namespace App\Message;

use Soneso\StellarSDK\Responses\Trades\TradeResponse;

class StoringTrade
{
    public function __construct(
        private TradeResponse $tradeResponse,
    ) {
    }

    public function getTradeResponse(): TradeResponse
    {
        return $this->tradeResponse;
    }
}
