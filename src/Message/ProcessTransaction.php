<?php

namespace App\Message;

use Soneso\StellarSDK\Responses\Transaction\TransactionResponse;

class ProcessTransaction
{
    public function __construct(
        private TransactionResponse $transactionResponse,
    ) {
    }

    public function getTransactionResponse(): TransactionResponse
    {
        return $this->transactionResponse;
    }
}
