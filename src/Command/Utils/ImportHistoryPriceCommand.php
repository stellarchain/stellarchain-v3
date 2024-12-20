<?php

namespace App\Command\Utils;

use App\Config\Timeframes;
use App\Entity\AggregatedMetrics;
use App\Config\Metric;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use League\Csv\Reader;

#[AsCommand(
    name: 'utils:market-import-xlm-price',
    description: 'Import XLM price from csv file.',
)]
class ImportHistoryPriceCommand extends Command
{
    public function __construct(
        public EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $csv = Reader::createFromPath('xlm-usd-max.xls', 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $dateTime = new \DateTimeImmutable($record['snapped_at']);
            $name = '';
            $value = 0;
            if (isset($record['market_cap'])) {
                $name = 'market-cap';
                $value = $record['market_cap'];
            }

            if (isset($record['price'])) {
                $name = 'price-usd';
                $value = $record['price'];
            }

            if (isset($record['total_volume'])) {
                $name = 'volume-24h';
                $value = $record['total_volume'];
            }

            $this->buildMetric(
                $name,
                $value,
                $dateTime
            );
        }

        $io->success('We added all daily prices for XLM');

        return Command::SUCCESS;
    }

    public function buildMetric($key, $value, $timestamp): void
    {
        $metricEnum = Metric::fromString($key);
        $aggregateMetric = new AggregatedMetrics();
        $aggregateMetric
            ->setTotalEntries(1)
            ->setMetricId($metricEnum)
            ->setTotalValue($value)
            ->setAvgValue($value)
            ->setMaxValue($value)
            ->setMinValue($value)
            ->setCreatedAt($timestamp)
            ->setTimeframe(Timeframes::fromString('1d'));

        $this->entityManager->persist($aggregateMetric);
        $this->entityManager->flush();
    }
}
