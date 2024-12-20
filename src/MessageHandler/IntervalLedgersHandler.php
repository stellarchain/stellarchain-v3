<?php

namespace App\MessageHandler;

use App\Config\Timeframes;
use App\Config\Metric as MetricEnum;
use App\Entity\AggregatedMetrics;
use App\Entity\Horizon\ExpAssetStats;
use App\Entity\Horizon\HistoryTrades;
use App\Entity\Horizon\Offers;
use App\Message\ProcessInterval;
use App\Service\GlobalValueService;
use Doctrine\ORM\EntityManagerInterface;
use Soneso\StellarSDK\Asset;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Horizon\Accounts;
use App\Entity\Horizon\HistoryLedgers;
use App\Entity\Horizon\HistoryOperations;
use App\Entity\Horizon\HistoryTransactions;

#[AsMessageHandler(fromTransport: 'horizon')]
class IntervalLedgersHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ManagerRegistry $doctrine,
        private GlobalValueService $globalValueService,
    ) {
    }

    public function __invoke(ProcessInterval $interval): void
    {
        $this->dispatchProcessLedgers($interval->getStart(), $interval->getEnd());
    }

    public function dispatchProcessLedgers(\DateTime $start, \DateTime $end): void
    {
        $usdXlmPrice = $this->globalValueService->getPrice();
        $ledgersRepository = $this->doctrine->getRepository(HistoryLedgers::class, 'horizon');
        $ledgersResult = $ledgersRepository->findByClosedAtRange($start, $end);

        $ledgerSequences = [];
        $totalTransactionCount = 0;
        $totalOperationCount = 0;
        $totalSuccessfulTransactionCount = 0;
        $totalFailedTransactionCount = 0;
        $timeDifferences = [];
        $previousClosedAt = null;

        foreach ($ledgersResult as $ledger) {
            $ledgerSequences[] = $ledger->getSequence();
            $totalTransactionCount += $ledger->getTransactionCount() + $ledger->getFailedTransactionCount();
            $totalOperationCount += $ledger->getOperationCount();
            $totalSuccessfulTransactionCount += $ledger->getSuccessfulTransactionCount();
            $totalFailedTransactionCount += $ledger->getFailedTransactionCount();
            $currentClosedAt = $ledger->getClosedAt()->getTimestamp();

            if ($previousClosedAt !== null) {
                $timeDifferences[] = $currentClosedAt - $previousClosedAt;
            }
            $previousClosedAt = $currentClosedAt;
        }

        $averageClosingTime = 0;
        if (count($timeDifferences) > 0) {
            $averageClosingTime = array_sum($timeDifferences) / count($timeDifferences);
        }


        $transactions = $this->getTransactions($ledgerSequences);
        $operationsRepository = $this->doctrine->getRepository(HistoryOperations::class, 'horizon');
        $transactionsRepository = $this->doctrine->getRepository(HistoryTransactions::class, 'horizon');
        $accountsRepository = $this->doctrine->getRepository(Accounts::class, 'horizon');
        $expAssetStatsRepository = $this->doctrine->getRepository(ExpAssetStats::class, 'horizon');
        $tradesRepository = $this->doctrine->getRepository(HistoryTrades::class, 'horizon');
        $offersRepository = $this->doctrine->getRepository(Offers::class, 'horizon');
        $tradeStats = $tradesRepository->getTradeStats($start, $end);

        $xdrAssets = array_map(function ($stat) {
            $asset = Asset::create($stat['asset_type'], $stat['asset_code'], $stat['asset_issuer']);
            $assetXdr = base64_encode($asset->toXdr()->encode());

            return $assetXdr;
        }, $tradeStats);

        $native = Asset::create(Asset::TYPE_NATIVE);
        $nativeXdr = base64_encode($native->toXdr()->encode());

        $offers = $offersRepository->getOffersByAssets($xdrAssets, $nativeXdr);

        $volumeXlm = 0;
        foreach ($tradeStats as $stat) {
            if ($stat['asset_type'] === Asset::TYPE_NATIVE) {
                $volumeXlm += $stat['volume'];
                continue;
            }
            $asset = Asset::create($stat['asset_type'], $stat['asset_code'], $stat['asset_issuer']);
            $assetXdr = base64_encode($asset->toXdr()->encode());
            if (array_key_exists($assetXdr, $offers) && isset($offers[$assetXdr]['price'])) {
                $volumeXlm += $stat->volume * $offers[$assetXdr]['price'];
            }
        }

        $invocations = $operationsRepository->getTotalContractInvocations($transactions);
        $fees = $transactionsRepository->getTotalFees($ledgerSequences);

        $metricsValues = [
            'ledgers' => count($ledgerSequences),
            'tps' => $totalTransactionCount / $averageClosingTime,
            'ops' => $totalOperationCount / $averageClosingTime,
            'tx-ledger' => $totalTransactionCount / count($ledgerSequences),
            'tx-success' => $totalSuccessfulTransactionCount,
            'tx-failed' => $totalFailedTransactionCount,
            'ops-ledger' => $totalOperationCount / count($ledgerSequences),
            'transactions' => $totalTransactionCount,
            'operations' => $totalOperationCount,
            'avg-ledger-sec' => $averageClosingTime,
            'trades' => $tradesRepository->totalTrades($start, $end),
            'output-value' => $operationsRepository->getTotalOutput($transactions),
            'xml-total-pay' => $operationsRepository->getXmlPayments($transactions),
            'dex-vol' => round($volumeXlm * $usdXlmPrice, 0),
            'invocations' => $invocations['invoke_contract'],
            'contracts' => $invocations['create_contract'],
            'fee-charged' => $fees['total_fee_charged'],
            'max-fee' => $fees['total_max_fee'],
        ];

        $dailyMetrics = [
            'assets' => $expAssetStatsRepository->totalAssets(),
            'accounts' => $accountsRepository->totalAccounts(),
            'top-accounts' => $accountsRepository->averageBalanceAccounts(100),
            'active-addresses' => $accountsRepository->activeAddressesCount(),
            'inactive-addresses' => $accountsRepository->inactiveAddressesCount(),
        ];

        $metricsValues = array_merge($metricsValues, $dailyMetrics);

        foreach ($metricsValues as $metric => $value) {
            $metricEnum = MetricEnum::fromString($metric);
            $this->buildMetric($metricEnum, $value, $end);
        }
    }

    public function buildMetric($metricEnum, $value, $timestamp): void
    {
        $batchStartDateImmutable = \DateTimeImmutable::createFromMutable($timestamp);
        $aggregateMetric = new AggregatedMetrics();
        $aggregateMetric
            ->setTotalEntries(1)
            ->setMetricId($metricEnum)
            ->setTotalValue($value)
            ->setAvgValue($value)
            ->setMaxValue($value)
            ->setMinValue($value)
            ->setCreatedAt($batchStartDateImmutable)
            ->setTimeframe(Timeframes::fromString('10m'));

        $this->entityManager->persist($aggregateMetric);
        $this->entityManager->flush();
    }

    private function getTransactions(array $ledgerIds): array
    {
        $transactionRepository = $this->doctrine->getRepository(HistoryTransactions::class, 'horizon');
        $transactions = $transactionRepository->findByLedgerIds($ledgerIds);

        $ids = array_column($transactions, 'id');
        $ids = array_map('intval', $ids);

        return $ids;
    }
}
