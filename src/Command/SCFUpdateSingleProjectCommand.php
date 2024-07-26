<?php

namespace App\Command;

use App\Entity\ProjectBrief;
use App\Integrations\StellarCommunityFund\GetRoundProject;
use App\Integrations\StellarCommunityFund\SCFConnector;
use App\Repository\ProjectBriefRepository;
use App\Repository\RoundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'scf:update-single-project',
    description: 'Add a short description for your command',
)]
class SCFUpdateSingleProjectCommand extends Command
{
    public function __construct(
        private RoundRepository $roundRepository,
        private ProjectBriefRepository $projectBriefRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rounds = $this->roundRepository->findAll();

        foreach ($rounds as $round) {
            foreach ($round->getProjects() as $project) {
                $projectBriefArray = $this->additionalContent($this->getRoundProject($project->getOriginalId()));
                foreach($projectBriefArray as $brief){
                    $now = new \DateTimeImmutable();
                    $projectBrief = $this->projectBriefRepository->findOneBy(['original_id' => $brief['id'], 'project' => $project]);
                    if (!$projectBrief){
                        $projectBrief = new ProjectBrief();
                    }
                    $projectBrief->setDescription($brief['description'])
                        ->setContent($brief['body'])
                        ->setLabel($brief['label'])
                        ->setOriginalId($brief['id'])
                        ->setProject($project)
                        ->setUpdatedAt($now);

                    $this->entityManager->persist($projectBrief);
                    $this->entityManager->flush();
                }
                $io->success('Projects successfully updated: '.$project->getName());
            }

        }


        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    public function getRoundProject(int $projectId): array
    {
        $connector = new SCFConnector();
        $roundProjectRequest = new GetRoundProject(projectId: $projectId);
        $responseData = $connector->send($roundProjectRequest)->json();

        return isset($responseData['additionalContent']) ? $responseData['additionalContent'] : [];
    }

    /**
     * @return array<int,array>
     *
     * @param array<int,mixed> $additionalContent
     */
    public function additionalContent(array $additionalContent): array
    {
        $projectBrief = [];
        foreach($additionalContent as $content){
            $projectBrief[] = [
                'id' => $content['formElement']['id'],
                'label' => $content['formElement']['label'],
                'description' => $content['formElement']['description'],
                'body' => $content['content']
            ];
        }
        return $projectBrief;
    }
}
