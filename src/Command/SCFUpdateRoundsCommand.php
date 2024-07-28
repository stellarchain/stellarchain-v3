<?php

namespace App\Command;

use App\Entity\SCF\Round;
use App\Entity\SCF\RoundPhase;
use DateTimeZone;
use DateTimeImmutable;
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
    name: 'scf:update-rounds',
    description: 'Add a short description for your command',
)]
class SCFUpdateRoundsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoundRepository $roundRepository,
        private RoundPhaseRepository $roundPhaseRepository
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
            ->setStartDate($this->arrayToDateTimeImmutable($roundData['startDate']))
            ->setEndDate($this->arrayToDateTimeImmutable($roundData['endDate']))
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
                ->setStartDate($this->arrayToDateTimeImmutable($roundPhaseData['startDate']))
                ->setEndDate($this->arrayToDateTimeImmutable($roundPhaseData['endDate']))
                ->setUpdatedAt($now);

            $this->entityManager->persist($roundPhase);
        }

        $this->entityManager->persist($round);
        $this->entityManager->flush();
    }

    function arrayToDateTimeImmutable(?array $dateTimeArray): ?DateTimeImmutable
    {
        if ($dateTimeArray) {
            $date = $dateTimeArray['date'];
            $timezone = new DateTimeZone($dateTimeArray['timezone']);

            return new DateTimeImmutable($date, $timezone);
        }
        return null;
    }
}
