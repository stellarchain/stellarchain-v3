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

#[AsMessageHandler(fromTransport: 'horizon')]
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

        $timeFrame = Timeframes::fromString('10m');

        $networkCharts = [
            /* 'total-accounts' => $accountsRepository->totalAccounts(), */
            'total-trades' => $tradesRepository->totalTrades($start, $end),
            'output-value' => $operationsRepository->getTotalOutput($transactions),
            'successful-transactions' => $totalSuccessfulTransactionCount,
            'operations-per-second' => $totalOperationCount / $averageClosingTime,
            /* 'transactions-value' => false, */
            'xml-total-payments' => $operationsRepository->getXmlPayments($transactions),
            'dex-volume' => round($volumeXlm * $usdXlmPrice, 0),
        ];

        $interval = $start->diff($end);
        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        if ($timeFrame->label() === '1d') {
            $networkCharts['total-assets'] = $expAssetStatsRepository->totalAssets();
            $networkCharts['total-accounts'] = $accountsRepository->totalAccounts();
        }

        foreach ($networkCharts as $metric => $value) {
            $this->buildMetric($timeFrame, 'network-charts', $metric, $value, $end);
        }

        $blockchainCharts = [
            'total-ledgers' => count($ledgerSequences),
            'failed-transactions' => $totalFailedTransactionCount,
            'transactions-per-second' => $totalTransactionCount / $averageClosingTime,
            'transactions-per-ledger' => $totalTransactionCount / count($ledgerSequences),
            'operations-per-ledger' => $totalOperationCount / count($ledgerSequences),
            'number-of-transactions' => $totalTransactionCount,
            'number-of-operations' => $totalOperationCount,
            'average-ledger-time' => $averageClosingTime,
            /* 'contract-invocations' => true, */
            /* 'created-contracts' => true, */
        ];

        foreach ($blockchainCharts as $metric => $value) {
            $this->buildMetric($timeFrame, 'blockchain-charts', $metric, $value, $end);
        }
    }

    public function buildMetric($timeframe, $chartType, $key, $value, $timestamp): void
    {
        $metric = new Metric();
        dump($timeframe, $chartType, $key, $value, $timestamp);
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
