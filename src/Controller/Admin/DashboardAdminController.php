<?php

namespace App\Controller\Admin;

use App\Entity\Coin;
use App\Entity\Comment;
use App\Entity\Community;
use App\Entity\Event;
use App\Entity\Feedback;
use App\Entity\Job;
use App\Entity\JobCategory;
use App\Entity\LedgerStat;
use App\Entity\Location;
use App\Entity\Post;
use App\Entity\Project;
use App\Entity\ProjectBrief;
use App\Entity\ProjectCategory;
use App\Entity\ProjectMember;
use App\Entity\ProjectType;
use App\Entity\Region;
use App\Entity\SCF\Round;
use App\Entity\SCF\RoundPhase;
use App\Entity\StellarHorizon\Asset;
use App\Entity\StellarHorizon\AssetMetric;
use App\Entity\StellarHorizon\Trade;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAdminController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(PostCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('StellarChain Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::subMenu('Posts', 'fas fa-list')->setSubItems([
            MenuItem::linkToCrud('Posts', 'fas fa-list', Post::class),
            MenuItem::linkToCrud('Comments', 'fas fa-list', Comment::class)
        ]);

        yield MenuItem::subMenu('SCF', 'fas fa-list')->setSubItems([
            MenuItem::linkToCrud('Projects', 'fas fa-list', Project::class),
            MenuItem::linkToCrud('Project Categories', 'fas fa-list', ProjectCategory::class),
            MenuItem::linkToCrud('Project Types', 'fas fa-list', ProjectType::class),
            MenuItem::linkToCrud('Project Members', 'fas fa-list', ProjectMember::class),
            MenuItem::linkToCrud('Project Information', 'fas fa-list', ProjectBrief::class),
            MenuItem::linkToCrud('SCF Rounds', 'fas fa-list', Round::class),
            MenuItem::linkToCrud('SCF Rounds Phases', 'fas fa-list', RoundPhase::class),
        ]);

        yield MenuItem::subMenu('Jobs', 'fas fa-list')->setSubItems([
            MenuItem::linkToCrud('Jobs', 'fas fa-list', Job::class),
            MenuItem::linkToCrud('Categories', 'fas fa-list', JobCategory::class)
        ]);
        yield MenuItem::linkToCrud('Communities', 'fas fa-list', Community::class);
        yield MenuItem::linkToCrud('Events', 'fas fa-list', Event::class);

        yield MenuItem::subMenu('Market', 'fas fa-list')->setSubItems([
            MenuItem::linkToCrud('Coins', 'fas fa-list', Coin::class),
            MenuItem::linkToCrud('Assets', 'fas fa-list', Asset::class),
            MenuItem::linkToCrud('Assets Metrics', 'fas fa-list', AssetMetric::class),
            MenuItem::linkToCrud('Trades', 'fas fa-list', Trade::class),
        ]);


        yield MenuItem::subMenu('Statistics', 'fas fa-list')->setSubItems([
            MenuItem::linkToCrud('Ledger Stats', 'fas fa-list', LedgerStat::class),
        ]);

        yield MenuItem::linkToCrud('Feedbacks', 'fas fa-list', Feedback::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-list', User::class)->setPermission('ROLE_ADMIN');

        yield MenuItem::subMenu('Settings', 'fas fa-list')->setSubItems([
            MenuItem::linkToCrud('Locations', 'fas fa-list', Location::class),
            MenuItem::linkToCrud('Regions', 'fas fa-list', Region::class),
        ]);

    }

}
