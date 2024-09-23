<?php

namespace App\Controller\Admin;

use App\Entity\StellarHorizon\AssetMetric;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class AssetMetricCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AssetMetric::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            NumberField::new('price'),
            NumberField::new('volume_24h'),
            NumberField::new('volume_1h'),
            NumberField::new('circulating_supply'),
            NumberField::new('price_change_1h'),
            NumberField::new('total_trades'),
            AssociationField::new('asset')
        ];
    }
}
