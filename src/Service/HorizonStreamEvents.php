<?php

namespace App\Service;



use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\StreamTrades;
use Soneso\StellarSDK\Responses\Trades\TradeResponse;


class HorizonStreamEvents
{
    public function processEventStreamTrades($cursor): string
    {
        try {
            $connector = new HorizonConnector('public');
            $tradesRequest = new StreamTrades($cursor);
            $body = $connector->send($tradesRequest)->stream();
            while (!$body->eof()) {
                $line = '';
                $char = null;
                while ($char != "\n") {
                    $line .= $char;
                    $char = $body->read(1);
                }
                if (!$line) continue;
                // Ignore "data: hello" handshake
                if (str_starts_with($line, 'data: "hello"')) continue;
                // "data: byebye" if closed, restart
                if (str_starts_with($line, 'data: "byebye"')) break;
                // Ignore lines that don't start with "data: "
                $sentinel = 'data: ';
                if (!str_starts_with($line, $sentinel)) continue;

                // Remove sentinel prefix
                $json = substr($line, strlen($sentinel));
                $decoded = json_decode($json, true);
                if ($decoded) {
                    $tradeResponse = TradeResponse::fromJson($decoded);
                    if ($tradeResponse instanceof TradeResponse) {
                        $cursor = $tradeResponse->getPagingToken();
                    }
                }
            }
        } catch (\Exception $e) {
            sleep(10);
        } finally {
            gc_collect_cycles();
        }

        return $cursor;
    }
}
