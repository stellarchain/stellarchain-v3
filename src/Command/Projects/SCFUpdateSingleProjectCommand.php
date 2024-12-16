<?php

namespace App\Command\Projects;


use App\Entity\ProjectMember;
use App\Repository\ProjectMemberRepository;
use DateTimeImmutable;
use App\Entity\Project;
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
    name: 'projects:scf-update-single-project',
    description: 'Save all data for every project.',
)]
class SCFUpdateSingleProjectCommand extends Command
{
    public function __construct(
        private RoundRepository $roundRepository,
        private ProjectBriefRepository $projectBriefRepository,
        private ProjectMemberRepository $projectMemberRepository,
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
                $projectData = $this->getRoundProject($project->getOriginalId());

                if ($projectData) {
                    $this->additionalContent(isset($projectData['additionalContent']) ? $projectData['additionalContent']: [], $project);
                    $this->getTeam(isset($projectData['team']) ? $projectData['team']: [], $project);
                    $io->success('Projects successfully updated: ' . $project->getName());
                }
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

        return $connector->send($roundProjectRequest)->json();
    }

    /**
     * @param array<int,mixed> $additionalContent
     */
    public function additionalContent(array $additionalContent, Project $project): void
    {
        foreach ($additionalContent as $content) {
            $brief = [
                'id' => $content['formElement']['id'],
                'label' => $content['formElement']['label'],
                'description' => $content['formElement']['description'],
                'body' => $content['content']
            ];

            $projectBrief = $this->projectBriefRepository->findOneBy(['original_id' => $brief['id'], 'project' => $project]);
            if (!$projectBrief) {
                $projectBrief = new ProjectBrief();
            }
            $projectBrief->setDescription($brief['description'])
                ->setContent($brief['body'])
                ->setLabel($brief['label'])
                ->setOriginalId($brief['id'])
                ->setProject($project)
                ->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($projectBrief);
            $this->entityManager->flush();
        }
    }

    /**
     * @param array<int,mixed> $teamData
     */
    public function getTeam(array $teamData, Project $project): void
    {
        foreach ($teamData as $teamMember) {
            $projectMember = $this->projectMemberRepository->findOneBy(['original_id' => $teamMember['user']['id']]);

            if (!$projectMember) {
                $projectMember = new ProjectMember();
            }

            $nickname = $teamMember['user']['nickname'];
            if (!isset($teamMember['user']['nickname'])) {
                $nickname = $teamMember['user']['displayName'];
            }

            $projectMember->setName($teamMember['user']['displayName'])
                ->setNickname($nickname)
                ->addProject($project)
                ->setOriginalId($teamMember['user']['id']);

            $this->entityManager->persist($projectMember);
            $this->entityManager->flush();
        }
    }
}
