<?php

namespace App\MessageHandler;

use App\Config\Timeframes;
use App\Entity\Horizon\ExpAssetStats;
use App\Entity\Horizon\HistoryTrades;
use App\Entity\Horizon\Offers;
use App\Entity\Metric;
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

#[AsMessageHandler]
class ProcessLedgersHandler
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

    public function dispatchProcessLedgers($start, $end)
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

        $metrics = [
            'total-accounts' => $accountsRepository->totalAccounts(),
            'total-assets' => $expAssetStatsRepository->totalAssets(),
            'total-trades' => $tradesRepository->totalTrades($start, $end),
            'dex-volume' => round($volumeXlm * $usdXlmPrice, 0),
            'total-output' => $operationsRepository->getTotalOutput($transactions),
            'xml-total-payments' => $operationsRepository->getXmlPayments($transactions),
            'average-balance-accounts' => $accountsRepository->averageBalanceAccounts(),
            'active-addresses' => $accountsRepository->activeAddressesCount(),
            'inactive-addresses' => $accountsRepository->inactiveAddressesCount(),
            'total-transaction-count' => $totalTransactionCount,
            'total-operation-count' => $totalOperationCount,
            'total-successful-transaction-count' => $totalSuccessfulTransactionCount,
            'total-failed-transaction-count' => $totalFailedTransactionCount,
            'total-ledgers' => count($ledgerSequences),
            'average-closing-time' => $averageClosingTime,
            'transactions-per-second' => $totalTransactionCount / $averageClosingTime,
            'transactions-per-ledger' => $totalTransactionCount / count($ledgerSequences),
        ];


        dd($metrics);

        $timeFrame = Timeframes::fromString('10m');

        foreach ($metrics as $key => $value) {
            $this->buildMetric($timeFrame, 'blockchain-charts', $key, $value, $end);
        }
    }

    public function buildMetric($timeframe, $chartType, $key, $value, $timestamp): void
    {
        $metric = new Metric();
        $metric->setChartType($chartType)
            ->setTimeframe($timeframe)
            ->setValue($value)
            ->setTimestamp($timestamp)
            ->setMetric($key);

        $this->entityManager->persist($metric);
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
