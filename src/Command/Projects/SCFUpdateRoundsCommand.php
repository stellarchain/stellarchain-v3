<?php

namespace App\Command\Projects;

use App\Entity\SCF\Round;
use App\Entity\SCF\RoundPhase;
use App\Utils\Helper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Integrations\StellarCommunityFund\GetRoundData;
use Doctrine\Common\Collections\ArrayCollection;
use App\Integrations\StellarCommunityFund\SCFConnector;
use App\Repository\RoundPhaseRepository;
use App\Repository\RoundRepository;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'projects:scf-update-rounds',
    description: 'Update SCF rounds.',
)]
class SCFUpdateRoundsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoundRepository $roundRepository,
        private RoundPhaseRepository $roundPhaseRepository,
        private Helper $helper
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $connector = new SCFConnector();
        $roundsRequest = new GetRoundData();
        $responseData = $connector->send($roundsRequest)->json();
        $roundsData = new ArrayCollection(isset($responseData['data']) ? $responseData['data'] : []);

        foreach ($roundsData as $roundData) {
            $this->processRound($roundData);
        }

        $io->success('All rounds and rounds phases saved.');

        return Command::SUCCESS;
    }

    /**
     * @return void
     * @param array<int,mixed> $roundData
     */
    private function processRound(array $roundData): void
    {
        $now = new \DateTimeImmutable();

        $round = $this->roundRepository->findOneBy(['original_id' => $roundData['id']]);
        if (!$round) {
            $round = new Round();
            $round->setCreatedAt($now);
        }

        $round->setName($roundData['name'])
            ->setOriginalId($roundData['id'])
            ->setDescription($roundData['description'])
            ->setImage($roundData['teaser'])
            ->setStartDate($this->helper->arrayToDateTimeImmutable($roundData['startDate']))
            ->setEndDate($this->helper->arrayToDateTimeImmutable($roundData['endDate']))
            ->setUpdatedAt($now);

        foreach ($roundData['phases'] as $roundPhaseData) {
            $now = new \DateTimeImmutable();

            $roundPhase = $this->roundPhaseRepository->findOneBy(['original_id' => $roundPhaseData['id']]);
            if (!$roundPhase) {
                $roundPhase = new RoundPhase();
                $roundPhase->setCreatedAt($now);
            }

            $roundPhase->setName($roundPhaseData['name'])
                ->setOriginalId($roundPhaseData['id'])
                ->setRound($round)
                ->setDescription($roundPhaseData['primaryStep']['text'])
                ->setStartDate($this->helper->arrayToDateTimeImmutable($roundPhaseData['startDate']))
                ->setEndDate($this->helper->arrayToDateTimeImmutable($roundPhaseData['endDate']))
                ->setUpdatedAt($now);

            $this->entityManager->persist($roundPhase);
        }

        $this->entityManager->persist($round);
        $this->entityManager->flush();
    }

}
