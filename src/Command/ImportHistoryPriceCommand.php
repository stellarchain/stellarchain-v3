<?php

namespace App\Command;

use App\Config\Timeframes;
use App\Entity\Metric;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use League\Csv\Reader;

#[AsCommand(
    name: 'market:import-xlm-price',
    description: 'Add a short description for your command',
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
            $dateTime = new \DateTime($record['snapped_at']);

            $this->buildMetric(
                Timeframes::fromString('1d'),
                'market-charts',
                'price-usd',
                $record['price'],
                $dateTime
            );

            $this->buildMetric(
                Timeframes::fromString('1d'),
                'market-charts',
                'market-cap',
                $record['market_cap'],
                $dateTime
            );

            $this->buildMetric(
                Timeframes::fromString('1d'),
                'market-charts',
                'volume-24h',
                $record['total_volume'],
                $dateTime
            );
        }

        $io->success('We added all daily prices for XLM');

        return Command::SUCCESS;
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
}
