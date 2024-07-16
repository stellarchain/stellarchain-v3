<?php

namespace App\Controller\Admin;

use App\Entity\Coin;
use App\Entity\Comment;
use App\Entity\Community;
use App\Entity\Event;
use App\Entity\Job;
use App\Entity\JobCategory;
use App\Entity\Location;
use App\Entity\Post;
use App\Entity\Project;
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
        yield MenuItem::linkToCrud('Posts', 'fas fa-list', Post::class);
        yield MenuItem::linkToCrud('Coins', 'fas fa-list', Coin::class);
        yield MenuItem::linkToCrud('Projects', 'fas fa-list', Project::class);
        yield MenuItem::linkToCrud('Comments', 'fas fa-list', Comment::class);
        yield MenuItem::linkToCrud('Community', 'fas fa-list', Community::class);
        yield MenuItem::linkToCrud('Events', 'fas fa-list', Event::class);
        yield MenuItem::linkToCrud('Jobs', 'fas fa-list', Job::class);
        yield MenuItem::linkToCrud('Job categories', 'fas fa-list', JobCategory::class);
        yield MenuItem::linkToCrud('Locations', 'fas fa-list', Location::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-list', User::class)->setPermission('ROLE_ADMIN');
    }

}
