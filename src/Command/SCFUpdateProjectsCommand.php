<?php

namespace App\Command;

use App\Config\AwardType;
use App\Config\ProjectStatus;
use App\Entity\Project;
use App\Entity\ProjectType;
use DateTimeZone;
use DateTimeImmutable;
use App\Integrations\StellarCommunityFund\GetRoundProjects;
use App\Integrations\StellarCommunityFund\SCFConnector;
use App\Repository\ProjectRepository;
use App\Repository\ProjectTypeRepository;
use App\Repository\RoundPhaseRepository;
use App\Repository\RoundRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;


#[AsCommand(
    name: 'scf:update-projects',
    description: 'Retrieve and update Stellar Community Fund projects',
)]
class SCFUpdateProjectsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectRepository $projectRepository,
        private ProjectTypeRepository $projectTypeRepository,
        private RoundRepository $roundRepository,
        private RoundPhaseRepository $roundPhaseRepository,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $rounds = $this->roundRepository->findAll();

        foreach ($rounds as $round) {
            foreach ($round->getRoundPhases() as $phase) {
                $phaseId = $phase->getOriginalId();

                $page = 1;
                $totalPages = 1;

                while ($page <= $totalPages) {
                    $response = $this->getRoundProjects($phaseId, $page);
                    $roundProjects = $response['data'];
                    $totalPages = $response['totalPages'];

                    foreach ($roundProjects as $project) {
                        $this->processProject($project);
                    }

                    $page++;
                }
            }

            $io->success('Phase synced.' . $round->getName());
        }

        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    public function getRoundProjects(int $phaseId, int $page): array
    {
        $connector = new SCFConnector();
        $roundProjectRequest = new GetRoundProjects(page: $page, phaseId: $phaseId);
        $responseData = $connector->send($roundProjectRequest)->json();

        $roundProjectsData = isset($responseData['data']) ? new ArrayCollection($responseData['data']) : new ArrayCollection();
        $totalPages = $responseData['totalPages'] ?? 1;

        return ['data' => $roundProjectsData, 'totalPages' => $totalPages];
    }

    /**
     * Process a single project.
     *
     * @param array $projectData
     */
    public function processProject(array $projectData): void
    {

        $additionalContentLabels = [];
        foreach ($projectData['additionalContent'] as $additionalContent) {
            if ($additionalContent['formElement']) {
                $key = $additionalContent['formElement']['label'];

            }
            if (!$additionalContent['formElementOption']) {
                $additionalContentLabels[$key] = $additionalContent['content'];
            }
            if ($additionalContent['formElementOption']) {
                $additionalContentLabels[$additionalContent['formElement']['label']] = $additionalContent['formElementOption']['label'];
            }
        }

        $awardType = null;
        $type = null;
        $budget = 0;

        foreach ($additionalContentLabels as $key => $label) {
            if (strpos($key, 'Budget') !== false) {
                $budget = $label;
            }
            if (strpos($key, 'Product Type') !== false || strpos($key, 'Project Type') !== false) {
                $type = $label;
            }

            if (strpos($key, 'Award Type') !== false) {
                $awardType = $label;
            }
        }

        $statusEnum = ProjectStatus::fromString($projectData['status']['processStep']);
        $awardEnum = AwardType::fromString($awardType);

        $projectType = $this->projectTypeRepository->findOneBy(['name' => $type]);
        if (!$projectType) {
            $projectType = new ProjectType();
            $projectType->setName($type);
            $this->entityManager->persist($projectType);
            $this->entityManager->flush();
        }

        $round = $this->roundRepository->findOneBy(['original_id' => $projectData['projectId']]);
        $roundPhase = $this->roundPhaseRepository->findOneBy(['original_id' => $projectData['phaseId']]);
        $user = $this->userRepository->findOneBy(['id' => 1]);
        $project = $this->projectRepository->findOneBy(['original_id' => $projectData['id']]);
        if (!$project) {
            $project = new Project();
        }
        $project->setName($projectData['title'])
            ->setOriginalId($projectData['id'])
            ->setUser($user)
            ->setContent($projectData['content'])
            ->setBudget($budget)
            ->setRound($round)
            ->setType($projectType)
            ->setAwardType($awardEnum->value)
            ->setRoundPhase($roundPhase)
            ->setStatus($statusEnum->value)
            ->setScfUrl($projectData['relativeUrl'])
            ->setScore($projectData['score'])
            ->setCreatedAt($this->arrayToDateTimeImmutable($projectData['created']))
            ->setUpdatedAt($this->arrayToDateTimeImmutable($projectData['updated']));

        $imageFile = null;
        foreach ($projectData['media'] as $media) {
            $imageFile = $media['imageUrl1920x1080'];

            $tempDirectory = sys_get_temp_dir();
            $tempFilePath = $tempDirectory . DIRECTORY_SEPARATOR . uniqid('image_', true) . '.jpg';
            try {
                $imageContent = file_get_contents($imageFile);
                if ($imageContent === false) {
                    throw new \Exception("Failed to download image.");
                }
                file_put_contents($tempFilePath, $imageContent);

                $project->setImageFile(new ReplacingFile($tempFilePath));
            } catch (\Exception $exception) {
            }
            break;
        }

        $this->entityManager->persist($project);
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

    function normalizeString(string $input): string
    {
        $lowercaseString = strtolower($input);
        $normalizedString = str_replace(' ', '-', $lowercaseString);
        return $normalizedString;
    }
}
