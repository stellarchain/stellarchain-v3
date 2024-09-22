<?php

namespace App\Controller\Admin;

use App\Entity\LedgerStat;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class LedgerStatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LedgerStat::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('ledger_id'),
            IntegerField::new('lifetime'),
            IntegerField::new('operations'),
            IntegerField::new('successful_transactions'),
            IntegerField::new('failed_transactions'),
            IntegerField::new('created_contracts'),
            IntegerField::new('contract_invocations'),
            IntegerField::new('transactions_second'),
            DateTimeField::new('created_at'),
        ];
    }
}
