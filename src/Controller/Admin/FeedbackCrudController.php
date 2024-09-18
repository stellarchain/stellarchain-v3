<?php

namespace App\Controller\Admin;

use App\Entity\Feedback;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class FeedbackCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feedback::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index','Feedbacks and Reports')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user_id')->autocomplete(),
            TextEditorField::new('message'),
            DateTimeField::new('created_at')->onlyOnIndex(),
        ];
    }
}
