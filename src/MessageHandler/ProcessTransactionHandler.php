<?php

namespace App\MessageHandler;

use App\Service\TransactionBatchService;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Xdr\XdrEnvelopeType;
use Soneso\StellarSDK\Xdr\XdrHostFunctionType;
use Soneso\StellarSDK\Xdr\XdrOperationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
class ProcessTransactionHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TransactionBatchService $transactionBatchService,
    ) {
    }

    public function __invoke(): void
    {
        $transaction = $transactionResponse->getTransactionResponse();
        if ($this->transactionBatchService->getLedgerId() === 0) {
            $this->transactionBatchService->setLedgerId($transaction->getLedger());
        }
        $envelope = $transaction->getEnvelopeXdr();
        $envelopeType = $envelope->getType()->getValue();

        if ($transaction->isSuccessful()) {
            $this->transactionBatchService->increaseTotalSuccessfulTransactions();
        } else {
            $this->transactionBatchService->increaseTotalFailedTransactions();
        }

        if ($envelopeType == XdrEnvelopeType::ENVELOPE_TYPE_TX) {
            $operations = $envelope->getV1()->getTx()->getOperations();
            $this->scanOperationTypes($operations);
            $this->transactionBatchService->increaseTotalOperations(count($operations));
        }

        $this->transactionBatchService->updateLedgerId($transaction->getLedger());
    }

    /**
     * @param array<int,mixed> $operations
     */
    public function scanOperationTypes(array $operations): void
    {
        foreach ($operations as $operation) {
            $operationBody = $operation->getBody();

            switch ($operationBody->getType()->getValue()) {
                case XdrOperationType::INVOKE_HOST_FUNCTION:
                    $invokeHostFunctionOperation = $operationBody->getInvokeHostFunctionOperation();
                    $hostFunctionType = $invokeHostFunctionOperation->getHostFunction()->getType()->getValue();

                    switch ($hostFunctionType) {
                        case XdrHostFunctionType::HOST_FUNCTION_TYPE_CREATE_CONTRACT:
                            $this->transactionBatchService->increaseTotalContractCreated();
                            break;

                        case XdrHostFunctionType::HOST_FUNCTION_TYPE_INVOKE_CONTRACT:
                            $this->transactionBatchService->increaseTotalContractInvocations();
                            break;

                        default:
                            break;
                    }
                    break;

                case XdrOperationType::CREATE_ACCOUNT:
                    $createAccountOp = $operationBody->getCreateAccountOp();
                    $amount = $createAccountOp->getStartingBalance();
                    break;

                case XdrOperationType::ACCOUNT_MERGE:
                    $accountMergeOp = $operationBody->getAccountMergeOp();
                    $amount = 100;
                    break;

                case XdrOperationType::PATH_PAYMENT_STRICT_RECEIVE:
                    $pathPaymentStrictReceiveOp = $operationBody->getPathPaymentStrictReceiveOp();
                    $amount = $pathPaymentStrictReceiveOp->getDestAmount();
                    break;

                case XdrOperationType::PATH_PAYMENT_STRICT_SEND:
                    $pathPaymentStrictSendOp = $operationBody->getPathPaymentStrictSendOp();
                    $amount = $pathPaymentStrictSendOp->getSendAmount();
                    break;

                case XdrOperationType::PAYMENT:
                    $paymentOp = $operationBody->getPaymentOp();
                    $amount = $paymentOp->getAmount();
                    break;
                default:
                    break;
            }
        }
    }
}
