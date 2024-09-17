<?php

namespace App\Controller\Admin;

use App\Entity\StellarHorizon\Trade;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TradeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Trade::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('base_asset'),
            AssociationField::new('counter_asset'),
            NumberField::new('price')->setNumDecimals(9),
        ];
    }
}
