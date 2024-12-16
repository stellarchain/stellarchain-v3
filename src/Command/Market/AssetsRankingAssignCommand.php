<?php

namespace App\Command\Market;

use App\Repository\StellarHorizon\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'market:assets-assign-ranks',
    description: 'Take each asset and give it a rank.',
)]
class AssetsRankingAssignCommand extends Command
{
    public function __construct(
        private AssetRepository $assetRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $offset = 0;
        $batchSize = 20;
        $counter = 1;
        do {
            $assets = $this->assetRepository->findBy(['in_market' => true], ['rank_raw' => 'DESC'], $batchSize, $offset);
            foreach ($assets as $asset) {
                $asset->setRank($counter);
                $counter++;
                $this->entityManager->persist($asset);
            }
            $offset += $batchSize;

            $this->entityManager->flush();
            $this->entityManager->clear();
        } while (count($assets) > 0);

        $io->success('Assign Ranks successfully.');

        return Command::SUCCESS;
    }
}
