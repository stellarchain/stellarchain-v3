<?php

namespace App\Controller\Admin;

use App\Config\AwardType;
use App\Config\ProjectStatus;
use App\Entity\Project;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            Field::new('imageFile')->setFormType(VichImageType::class)->onlyOnForms(),
            ImageField::new('imageFile')->onlyOnIndex(),
            TextField::new('name'),
            BooleanField::new('essential'),
            ChoiceField::new('award_type')->setChoices(AwardType::cases())->onlyOnIndex(),
            ChoiceField::new('status')->setChoices(ProjectStatus::cases())->onlyOnIndex(),
            AssociationField::new('user')->autocomplete()->onlyOnForms(),
            AssociationField::new('type')->autocomplete(),
            AssociationField::new('round')->autocomplete()->onlyOnIndex(),
            AssociationField::new('round_phase')->autocomplete()->onlyOnForms(),
            MoneyField::new('budget')->setCurrency('USD')->setStoredAsCents(false)->setNumDecimals(0),
            TextField::new('description')->onlyOnForms(),
            TextEditorField::new('content')->onlyOnForms(),
            TextField::new('scf_url')->onlyOnForms(),
            IntegerField::new('score')->onlyOnDetail(),

            DateTimeField::new('created_at')->onlyOnDetail(),
            DateTimeField::new('updated_at')->onlyOnIndex(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('round')
            ->add('award_type')
            ->add('budget')
            ->add('type')
            ->add('status');
    }
}
