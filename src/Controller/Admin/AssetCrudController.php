<?php

namespace App\Controller\Admin;

use App\Entity\StellarHorizon\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AssetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Asset::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('asset_code'),
            TextField::new('asset_type'),
            TextField::new('asset_issuer'),
            IntegerField::new('num_claimable_balances')->onlyOnForms(),
            IntegerField::new('num_contracts')->onlyOnForms(),
            IntegerField::new('num_liquidity_pools')->onlyOnForms(),
            IntegerField::new('claimable_balances_amount')->onlyOnForms(),
            IntegerField::new('contracts_amount')->onlyOnForms(),
            IntegerField::new('liquidity_pools_amount')->onlyOnForms(),
            IntegerField::new('amount'),
            IntegerField::new('num_accounts')->onlyOnForms(),
            IntegerField::new('num_archived_contracts')->onlyOnForms(),
            IntegerField::new('archived_contracts_amount')->onlyOnForms(),
            ArrayField::new('accounts')->onlyOnDetail(),
            BooleanField::new('in_market'),
            DateTimeField::new('created_at')->onlyOnDetail(),
            DateTimeField::new('updated_at')->onlyOnIndex(),
        ];
    }
}
